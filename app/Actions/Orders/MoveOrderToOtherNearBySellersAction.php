<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\Products;
use App\Models\ProductsByBuyer;
use App\Models\User;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\ProductServices;
use App\Services\TwilioSmsServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class MoveOrderToOtherNearBySellersAction
{
    private Orders|OrdersFromOtherSeller $order;

    private User $seller;

    private OrderItems|null $orderItem;

    private array $nearbySellers;

    private string $productType;

    private int $nearByMiles = 3;

    public function execute(Orders|OrdersFromOtherSeller $order, User $seller, ?OrderItems $orderItem = null): true
    {
        $this->order = $order;
        $this->seller = $seller;
        $this->orderItem = $orderItem;

        if ($this->order instanceof Orders && $this->getOrderStatus() == OrderStatusEnum::PENDING->value) {
            $this->setProductType($this->order);

            if ($this->productType == (new ProductsByBuyer)->getMorphClass()) {
                $this->updateInitialTotal();
                $this->setNearbySellers($this->seller);
                $this->moveOrderToNearbySellers($this->nearbySellers);
            }

            if ($this->productType == (new Products)->getMorphClass() && isset($this->orderItem)) {
                $this->moveOrderItemToGivenNearBySeller($this->seller->id);
            }
        }

        if ($this->order instanceof OrdersFromOtherSeller && $this->getOrderStatus() == OrderStatusEnum::PENDING->value) {
            $this->setNearbySellers($this->seller);
            $this->moveOrderFromOtherSellerToRandomNearBySeller($this->nearbySellers);
        }

        return true;
    }

    private function setProductType(Orders|OrdersFromOtherSeller $order): void
    {
        $this->productType = ($order instanceof Orders) ?
            $order->order_items[0]->product_belongs_to_type :
            $order->product_belongs_to_type;
    }

    private function setNearbySellers(User $seller): void
    {
        /* Get sellers who belongs to the city of this store owner */
        $sellersOfSameCity = $this->getSellersOfSameCity();
        /* Get sellers who are nearby to the order placing buyer */
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $this->order->customer_lat,
            $this->order->customer_lon,
            $sellersOfSameCity,
            $seller->id,
            nearByMiles: $this->nearByMiles,
        );

        if (empty($this->nearbySellers)) {
            throw new Exception('No nearby sellers found');
        }

        $this->logNearBySellers();
    }

    private function getSellersOfSameCity(): Collection
    {
        return Cache::remember(
            'getSellersOfSameCity' . $this->seller->id,
            Carbon::now()->addDay(),
            fn() => User::getActiveAndBlockedParentAndChildSellersByCity(
                $this->seller->city,
                $this->seller->id,
            )
        );
    }

    private function getSellersOfSameCityAndCategory(): Collection
    {
        return Cache::remember(
            'getSellersOfSameCityAndCategory' . $this->seller->id,
            Carbon::now()->addDay(),
            fn() => User::getActiveAndBlockedParentAndChildSellersByCityAndCategory(
                $this->seller->city,
                $this->getProductCategoryId(),
                $this->seller->id,
            )
        );
    }

    private function getProductCategoryId(): int
    {
        if ($this->order instanceof Orders) {
            return $this->order->order_items[0]->product->category_id;
        }
        /* OrdersFromOtherSeller has morph relation "product" */
        return $this->order->product->category_id;
    }

    private function getOrderStatus(): string
    {
        /* Run a fresh query to get the updated order_status */
        return ($this->order instanceof Orders) ?
            Orders::getById($this->order->id, ['order_status'])->order_status :
            OrdersFromOtherSeller::getById($this->order->id, ['order_status'])->order_status;
    }

    private function logNearBySellers(): void
    {
        foreach ($this->nearbySellers as $singleIndex) {
            logger()->channel('nearBySellers')->info('Nearby Sellers Information:', [
                'id' => $singleIndex['id'],
                'email' => $singleIndex['email'],
                'business_name' => $singleIndex['business_name'],
            ]);
        }
        /* Add hyphens at the end of a log group */
        logger()->channel('nearBySellers')->info('-----------------------------------');
    }

    private function updateInitialTotal(): void
    {
        /* Update initial_total in case of ProductsByBuyer order */
        $this->order->initial_total = $this->order->order_items[0]->product_price * $this->order->order_items[0]->product_qty;
    }

    private function moveOrderToNearbySellers(array $nearbySellers): void
    {
        /* Send this product/order_item to all nearby sellers */
        foreach ($nearbySellers as $singleIndex) {
            $this->addIntoOrdersFromOtherSeller(
                order: $this->order,
                orderItem: $this->order->order_items[0],
                nearBySellerId: $singleIndex['id']
            );
            /* WhatsApp order details to nearby sellers */
            TwilioSmsServices::sendWhatsAppMessageWithMedia(
                receiverNumber: $singleIndex['country_code'] . $singleIndex['business_phone'],
                // receiverNumber: '+923170155625',
                message: $this->buildWhatsAppMessageBody(),
                mediaUrl: asset(config('constants.BUCKET') . $this->order->order_items[0]->product->feature_img)
            );
        }
        /* Email order details to nearby sellers */
        EmailServices::sendProductByBuyerOrderDetailsToNearBySellersMail(
            array_column($this->nearbySellers, 'email'),
            $this->order
        );
        /* Remove the whole parent order from the orders table in case of ProductsByBuyer order */
        Orders::remove($this->order->id);
    }

    private function moveOrderFromOtherSellerToRandomNearBySeller(array $nearbySellers): void
    {
        $randomIndex = array_rand($nearbySellers, 1);

        OrdersFromOtherSeller::moveToAnotherSeller($this->order->id, $nearbySellers[$randomIndex]['id']);

        OrdersFromOtherSeller::incrementTimesRejected($this->order->id);
    }

    private function moveOrderItemToGivenNearBySeller(int $nearBySellerId): void
    {
        /* Send this product to another seller */
        $this->addIntoOrdersFromOtherSeller(
            order: $this->order,
            orderItem: $this->orderItem,
            nearBySellerId: $nearBySellerId
        );
        /* Subtract the total price of this order_item/product from the current order's total */
        $productTotalPrice = $this->orderItem->product_price * $this->orderItem->product_qty;
        Orders::subFromOrderTotal(
            $this->orderItem->order_id,
            $productTotalPrice
        );
        /**
         * If there's only 1 item in the order, remove the whole order,
         * else only remove the selected item from current order items
         */
        ($this->order->order_items->count() == 1) ?
            Orders::remove($this->order->id) :
            OrderItems::remove($this->orderItem->id);
    }

    public function addIntoOrdersFromOtherSeller(Orders $order, OrderItems $orderItem, int $nearBySellerId): OrdersFromOtherSeller
    {
        return OrdersFromOtherSeller::add(
            $order->created_by_type,
            $order->created_by_id,
            $nearBySellerId,
            $order->id,
            $orderItem->product_belongs_to_type,
            $orderItem->product_belongs_to_id,
            $orderItem->product_price,
            $orderItem->product_qty,
            $order->initial_total,
            (float) $order->customer_lat ?? null,
            (float) $order->customer_lon ?? null,
            $order->customer_name,
            $order->country_code,
            $order->phone_number,
            $order->address,
            $order->house_no,
            $order->flat,
            $order->country,
            $order->state,
            $order->city,
            $order->postcode,
            $order->payment_intent_id,
            $order->driver_charges,
            $order->delivery_charges,
            $order->service_charges,
            $order->device,
            $order->type,
            $order->description,
            $order->payment_status,
            $order->offloading,
            $order->offloading_charges,
            now(),
            $order->created_at,
        );
    }

    private function buildWhatsAppMessageBody(): string
    {
        $product = $this->order->order_items[0]->product;
        $colors = ProductServices::jsonDecodeColors($product->colors);

        $message = "*Hi Dear Seller,*\n\n";
        $message .= "Can you help with a quick stock check please?\n\n";
        $message .= "If you can reply in the next 10–15 minutes, I can confirm and arrange a collection/delivery.\n\n";
        $message .= "*Please reply with:*\n";
        $message .= "• In stock? (Yes/No)\n";
        $message .= "• Price ex VAT (and inc VAT if easier)\n";
        $message .= "• Earliest collection time today\n\n";

        $message .= "*ITEM NEEDED:*\n";
        $message .= "─────────────────────────────\n";
        $message .= "*Product Name:* {$product->product_name}\n";
        $message .= "*Qty:* {$this->order->order_items[0]->product_qty}\n";
        $message .= "*Category:* {$product->category?->category_name}\n";
        $message .= "*Budget:* £{$product->max_price}\n";
        $message .= "*Weight:* {$product->weight}kg\n";
        $message .= "*Brand:* {$product->brand}\n";
        $message .= "*Part Number:* {$product->part_number}\n";
        $message .= "*Colors:* {$colors}\n";
        $message .= "*Transport Vehicle:* {$product->transport_vehicle}\n";
        $message .= "*Height:* {$product->height}\n";
        $message .= "*Width:* {$product->width}\n";
        $message .= "*Length:* {$product->length}\n";
        $message .= "─────────────────────────────\n\n";

        $message .= "Thanks,\n";
        $message .= "Azim\n";
        $message .= "Teek It\n";
        $message .= config('constants.HEAD_OFFICE_CONTACT');

        return $message;
    }
}
