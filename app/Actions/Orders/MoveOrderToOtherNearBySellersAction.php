<?php

namespace App\Actions;

use App\Enums\OrderStatusEnum;
use App\Jobs\SendProductByBuyerOrderDetailsToNearBySellersJob;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\Products;
use App\Models\ProductsByBuyer;
use App\Models\User;
use App\Services\GoogleMapServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class MoveOrderToOtherNearBySellersAction
{
    /**
     * 1st fetch all CUSTOM ORDERS of today which are still pending 
     * Now, check if their created_at time is more than 5 minutes ago
     * Then update their seller_id to another seller having products in the same category within 5 miles
     * After doing this process send a request to the frontend to refresh the orders list (Using Websockets)
     */
    private Orders|OrdersFromOtherSeller $order;

    private User $seller;

    private OrderItems|null $orderItem;

    private array $nearbySellers;

    private string $productType;

    private int $nearByMiles = 3;

    public function execute(Orders|OrdersFromOtherSeller $order, User $seller, ?OrderItems $orderItem = null): bool
    {
        $this->order = $order;
        $this->seller = $seller;
        $this->orderItem = $orderItem;

        if ($this->order instanceof Orders && $this->getOrderStatus() == OrderStatusEnum::PENDING->value) {
            $this->setProductType($this->order);

            if ($this->productType == (new ProductsByBuyer)->getMorphClass()) {
                $this->setNearbySellers($this->seller, $this->order);
                $this->moveOrderToNearbySellers($this->nearbySellers);
            }

            if ($this->productType == (new Products)->getMorphClass() && isset($orderItem)) {
                $this->moveOrderItemToGivenNearBySeller($orderItem, $this->order, $seller->id);
            }
        }

        if ($this->order instanceof OrdersFromOtherSeller && $this->getOrderStatus() == OrderStatusEnum::PENDING->value) {
            $this->setNearbySellers($seller, $this->order);
            $this->moveOrderToRandomNearBySeller($this->order, $this->nearbySellers);
        }

        return true;
    }

    private function setProductType(Orders|OrdersFromOtherSeller $order): void
    {
        $this->productType = ($order instanceof Orders) ?
            $order->order_items[0]->product_belongs_to_type :
            $order->product_belongs_to_type;
    }

    private function setNearbySellers(User $seller, Orders|OrdersFromOtherSeller $order): void
    {
        /* Get sellers who belongs to the city of this store owner */
        $sellersOfTheSameCityAndCategory = $this->getSellersOfSameCityAndCategory();
        /* Get sellers who are nearby to the order placing buyer */
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $order->customer_lat,
            $order->customer_lon,
            $sellersOfTheSameCityAndCategory,
            $seller->id,
            nearByMiles: $this->nearByMiles,
        );

        if (empty($this->nearbySellers)) {
            throw new Exception('No nearby sellers found');
        }
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

    private function moveOrderToNearbySellers(array $nearbySellers): void
    {
        /* Send this product/order_item to all nearby sellers */
        foreach ($nearbySellers as $singleIndex) {
            $this->addIntoOrdersFromOtherSeller(
                order: $this->order,
                orderItem: $this->order->order_items[0],
                nearBySellerId: $singleIndex['id']
            );
        }
        /* Remove the whole parent order from the orders table in case of ProductsByBuyer order */
        Orders::remove($this->order->id);
        /* Email order details to nearby sellers */
        SendProductByBuyerOrderDetailsToNearBySellersJob::dispatch(
            $this->nearbySellers,
            $this->order
        )->onQueue('high');
    }

    private function moveOrderToRandomNearBySeller(OrdersFromOtherSeller $order, array $nearbySellers): void
    {
        $randomIndex = array_rand($nearbySellers, 1);

        OrdersFromOtherSeller::moveToAnotherSeller($order->id, $nearbySellers[$randomIndex]['id']);

        OrdersFromOtherSeller::incrementTimesRejected($order->id);
    }

    private function moveOrderItemToGivenNearBySeller(OrderItems $orderItem, Orders $order, int $nearBySellerId): void
    {
        /* Send this product to another seller */
        $this->addIntoOrdersFromOtherSeller(
            order: $order,
            orderItem: $orderItem,
            nearBySellerId: $nearBySellerId
        );
        /* Subtract the total price of this order_item/product from the current order's total */
        $productTotalPrice = $orderItem->product_price * $orderItem->product_qty;
        Orders::subFromOrderTotal(
            $orderItem->order_id,
            $productTotalPrice
        );
        /**
         * If there's only 1 item in the order, remove the whole order,
         * else only remove the selected item from current order items
         */
        ($order->order_items->count() == 1) ? Orders::remove($order->id) : OrderItems::remove($orderItem->id);
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
}
