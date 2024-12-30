<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
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

class OrdersOfUniqueProductsLivewire extends Component
{
    use WithPagination;

    public
        $sellerId,
        $orderHoldingMinutes = 2,
        $priceBySeller,
        $selectedOrder;

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
        $this->resetAllErrors();

        $this->reset([
            'priceBySeller',
            'selectedOrder',
        ]);
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        // $this->resetValidation();
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
                return GoogleMapServices::findDistanceByMakingChunks(
                    $customerLat,
                    $customerLon,
                    $sellersOfSameCity,
                    10
                );

                // return GoogleMapServices::findDistanceByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellersOfSameCity, 10);
            }
        );
    }

    /* 
    * CRUD Methods
    */
    public function moveToAnotherSeller(
        $orderId,
        $orderStatus,
        $customerLat,
        $customerLon,
        $movedAt,
        $createdAt
    ) {
        try {
            /* Perform some operation */

            /* Get sellers who belong to the city of this store owner */
            $sellersOfTheSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearbySellers = $this->getNearBySellers($customerLat, $customerLon, $sellersOfTheSameCity);
            $randomIndex = array_rand($nearbySellers, 1);
            /* Update sellerId if the current order is older than 2 minutes */
            $moved = false;
            $movedAt = ($movedAt) ? $movedAt : $createdAt;
            if ($this->isTheOrderOlderThen($this->orderHoldingMinutes, $movedAt) && $orderStatus === 'pending') {
                // Orders::incrementTimesRejected($orderId);
                $moved = Orders::moveToAnotherSeller($orderId, $nearbySellers[$randomIndex]['id']);

                info("Order has been moved to seller: " . $nearbySellers[$randomIndex]['id']);
            }

            /* Operation finished */
            if ($orderStatus === 'pending') {
                if ($moved) {
                    session()->flash('success', 'Order#' . $orderId . ' has been moved to another seller.');
                } else {
                    session()->flash('warning', 'Soon Order#' . $orderId . ' will be moved to another seller.');
                }
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }


    public function rejectedBySeller($orderId, $customerLat, $customerLon)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellersOfTheSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearbySellers = $this->getNearBySellers($customerLat, $customerLon, $sellersOfTheSameCity);
            $randomIndex = array_rand($nearbySellers, 1);

            // Orders::incrementTimesRejected($orderId);
            $moved = Orders::moveToAnotherSeller($orderId, $nearbySellers[$randomIndex]['id']);

            /* Operation finished */
            if ($moved) {
                session()->flash('success', 'Order#' . $orderId . ' has been moved to another seller.');
            } else {
                session()->flash('warning', 'Sorry! Order#' . $orderId . ' has not been moved to another seller due to some technical error.');
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

            $newOrderTotal =  $this->priceBySeller * $this->selectedOrder->order_items[0]->product_qty;
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

            /*
                Note:
                Please remove the bugs related to the sendPickupYourOrderMail() email method
             */
            // dd(Orders::getById($orderId, ['id', 'created_by_id', 'seller_id']));

            if ($type == OrderTypeEnum::SELF_PICKUP->value) {
                $orderDetails = Orders::getById($orderId, ['id', 'created_by_id', 'seller_id']);
                EmailServices::sendPickupYourOrderMail($orderDetails);
            }
            /* Operation finished */
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
            /*
            * 1. Use the payment_intent_id for incremental authorization. 
            * 2. Check the diff between the original price & the current price if the
            * current price is greater then the original then perform incremental authorization & 
            * then capture the payment. Otherwise directly capture.
            */
            $this->selectedOrder = Orders::getById($orderId);
            // dd($this->selectedOrder);

            $currentDeliveryCharges = OrderServices::getDeliveryCharges(
                $this->selectedOrder->seller->lat,
                $this->selectedOrder->seller->lon,
                $this->selectedOrder->buyer->lat,
                $this->selectedOrder->buyer->lon,
                $this->selectedOrder->order_items[0]->product->weight,
            );
            // dd($currentDeliveryCharges);
            $currentTotalAmount = $this->selectedOrder->current_total + $this->selectedOrder->service_charges + $currentDeliveryCharges;
            $initialTotalAmount = $this->selectedOrder->initial_total + $this->selectedOrder->service_charges + $this->selectedOrder->delivery_charges;

            if ($currentTotalAmount > $initialTotalAmount) {
                $response = StripeServices::performIncrementalAuthorization(
                    $this->selectedOrder->payment_intent_id,
                    $currentTotalAmount,
                    false,
                )->getData();

                // dd($response);
                throw_if($response->data?->error, throw new Exception($response->data->error->message));

                $response = StripeServices::capturePaymentIntent(
                    $this->selectedOrder->payment_intent_id,
                    $currentTotalAmount,
                    false,
                );
            } else {
                /* Otherwise simply capture */
                $response = StripeServices::capturePaymentIntent(
                    $this->selectedOrder->payment_intent_id,
                    $currentTotalAmount,
                    false,
                )->getData();
            }

            // $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::DELIVERED);
            /* Operation finished */
            if ($response->status) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', $response->data->error->message);
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
        return view('livewire.sellers.orders-of-unique-products-livewire', ['data' => $data]);
    }
}
