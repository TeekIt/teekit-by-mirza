<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentIntentStatusEnum;
use App\Models\OrdersFromOtherSeller;
use App\OrderItems;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\OrderServices;
use App\Services\StripeServices;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;

/** @deprecated */
class OrdersOfUniqueProductsLivewire extends Component
{
    use WithPagination;

    public
        $sellerId,
        $priceBySeller,
        $selectedOrder,
        $orderId;

    public int $orderHoldingMinutes = 2;

    protected $paginationTheme = 'bootstrap';
    /* 
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->sellerId = auth()->id();
    }
    /* 
     * Helpers
     */
    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'priceBySeller',
            'selectedOrder',
            'orderId',
        ]);
    }

    public function renderAcceptOrderModal($orderId)
    {
        $this->resetModal();

        $this->selectedOrder = Orders::getById($orderId);

        $this->dispatchBrowserEvent('show-modal', ['id' => 'acceptOrderModal']);
    }

    public function isTheOrderOlderThen(int $theseMinutes, string $orderMovedAt)
    {
        return (Carbon::parse($orderMovedAt)->diffInMinutes(Carbon::now()) > $theseMinutes) ? true : false;
    }

    public function getSellersOfSameCity()
    {
        return Cache::remember(
            'getSellersOfSameCity' . $this->sellerId,
            Carbon::now()->addDay(),
            fn() => User::getParentAndChildSellersByCity(auth()->user()->city)
        );
    }

    public function getNearBySellers($customerLat, $customerLon, $sellersOfSameCity)
    {
        return Cache::remember(
            'getNearBySellers' . $this->sellerId . $customerLat . $customerLon,
            Carbon::now()->addDay(),
            function () use ($customerLat, $customerLon, $sellersOfSameCity) {
                /* 
                 * Add this function when moving to production/staging
                 * Bcz this function will not work with "faker" generated 
                 * customer lat, lon
                 */
                return GoogleMapServices::findNearByUsersByMakingChunks(
                    $customerLat,
                    $customerLon,
                    $sellersOfSameCity,
                    10
                );

                // return GoogleMapServices::findNearByUsersByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellersOfSameCity, 10);
            }
        );
    }

    public function noNearBySellers($orderId)
    {
        $this->orderId = $orderId;
        $this->dispatchBrowserEvent('show-modal', ['id' => 'noOtherSellersModal']);
    }

    /* 
     * CRUD Methods
     */
    public function sendItemToAnOtherSeller($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::getById($orderId);

            $orderTotalPrice = $this->selectedOrder->order_items[0]->product_price * $this->selectedOrder->order_items[0]->product_qty;
            /* Get sellers who belongs to the city of this store owner */
            $sellersOfTheSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearbySellers = $this->getNearBySellers(
                $this->selectedOrder->customer_lat,
                $this->selectedOrder->customer_lon,
                $sellersOfTheSameCity
            );

            if (empty($nearbySellers))
                return $this->noNearBySellers($orderId);

            $randomIndex = array_rand($nearbySellers, 1);

            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->selectedOrder->created_by_type,
                $this->selectedOrder->created_by_id,
                $nearbySellers[$randomIndex]['id'],
                $this->selectedOrder->order_items[0]->product_belongs_to_type,
                $this->selectedOrder->order_items[0]->product_belongs_to_id,
                $this->selectedOrder->order_items[0]->product_price,
                $this->selectedOrder->order_items[0]->product_qty,
                $orderTotalPrice,
                (float) $this->selectedOrder->customer_lat ?? null,
                (float) $this->selectedOrder->customer_lon ?? null,
                $this->selectedOrder->customer_name,
                $this->selectedOrder->phone_number,
                $this->selectedOrder->address,
                $this->selectedOrder->house_no,
                $this->selectedOrder->flat,
                $this->selectedOrder->country,
                $this->selectedOrder->state,
                $this->selectedOrder->city,
                $this->selectedOrder->postcode,
                $this->selectedOrder->payment_intent_id,
                $this->selectedOrder->driver_charges,
                $this->selectedOrder->delivery_charges,
                $this->selectedOrder->service_charges,
                $this->selectedOrder->device,
                $this->selectedOrder->type,
                $this->selectedOrder->description,
                $this->selectedOrder->payment_status,
                $this->selectedOrder->offloading,
                $this->selectedOrder->offloading_charges,
                now(),
                $this->selectedOrder->created_at,
            );

            /* Remove the item from current order items */
            $removed = OrderItems::remove($this->selectedOrder->order_items[0]->id);
            /* Subtract the total price of this product/order_item from the current order's total */
            $subtracted = Orders::subFromOrderTotal($this->selectedOrder->id, $orderTotalPrice);

            info('The current order has been sent to seller: ' . $nearbySellers[$randomIndex]['id']);
            /* Operation finished */
            sleep(1);

            if ($removed && $subtracted) {
                session()->flash('success', config('constants.SENT_TO_OTHER_STORE_SUCCESS'));
            } else {
                session()->flash('error', config('constants.SENT_TO_OTHER_STORE_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function acceptedBySeller()
    {
        $this->validate([
            'priceBySeller' => [
                'required',
                'numeric',
                'max:' . $this->selectedOrder->order_items[0]->product_price,
                'min:1',
            ],
        ]);

        try {
            /* Perform some operation */
            Orders::isViewed($this->selectedOrder->id);

            $newOrderTotal = $this->priceBySeller * $this->selectedOrder->order_items[0]->product_qty;
            $updated = Orders::updateInfo(
                id: $this->selectedOrder->id,
                currentTotal: $newOrderTotal,
                orderStatus: OrderStatusEnum::ACCEPTED
            );
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'acceptOrderModal']);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function readyBySeller($orderId, $type)
    {
        try {
            /* Perform some operation */
            $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::READY);

            // Note:
            // Please remove the bugs related to the sendPickupYourOrderMail() email method
           
            if ($type == OrderTypeEnum::SELF_PICKUP->value) {
                $orderDetails = Orders::getById($orderId, ['id', 'created_by_id', 'seller_id']);
                EmailServices::sendPickupYourOrderMail($orderDetails);
            }
            /* Operation finished */
            sleep(1);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function deliveredBySeller($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::getById($orderId);
            $totalWeight = $this->selectedOrder->order_items[0]->product->weight;

            $currentDeliveryCharges = OrderServices::getTotalDeliveryCharges(
                $this->selectedOrder->seller->lat,
                $this->selectedOrder->seller->lon,
                $this->selectedOrder->buyer->lat,
                $this->selectedOrder->buyer->lon,
                $totalWeight,
            );

            $currentTotalAmount = round($this->selectedOrder->current_total + $this->selectedOrder->service_charges + $currentDeliveryCharges);
          
            $initialTotalAmount = round($this->selectedOrder->initial_total + $this->selectedOrder->service_charges + $this->selectedOrder->delivery_charges);

            if ($currentTotalAmount <= $initialTotalAmount) {
                $response = StripeServices::capturePaymentIntent(
                    $this->selectedOrder->payment_intent_id,
                    StripeServices::calculateCharge($currentTotalAmount),
                );
                if (isset($response->error)) {
                    throw new Exception($response->error->message);
                }
            } else {
                throw new Exception('Your current order total should be equal to or less than the initial order total amount');
            }

            $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::DELIVERED);
            /* Operation finished */
            sleep(1);

            if ($updated && $response->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                session()->flash('success', config('constants.ORDER_DELIVERED_SUCCESSFULLY'));
            } else {
                session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function render()
    {
        $data = Orders::getOrdersOfUniqueProductsForView(
            sellerId: $this->sellerId,
            orderBy: 'desc',
        );

        return view('livewire.sellers.orders-of-unique-products-livewire', compact('data'));
    }
}
