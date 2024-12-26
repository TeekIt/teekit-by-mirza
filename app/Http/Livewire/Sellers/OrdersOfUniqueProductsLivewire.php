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
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;
use Stripe\Service\Climate\OrderService;
use Stripe\Stripe;

class OrdersOfUniqueProductsLivewire extends Component
{
    use WithPagination;

    public
        $sellerId,
        $orderHoldingMinutes = 999999,
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
    // public function showModal($modalId){
    //     $this->dispatchBrowserEvent('show-modal', ['id' => 'acceptOrderModal']);
    // }

    // public function setOrder($id)
    // {
    //     $this->selectedOrder = Orders::getById($id);
    //     // dd($this->selectedOrder->order_items[0]->product_price);
    // }

    public function isTheOrderOlderThen(int $these_minutes, string $order_created_at)
    {
        return (Carbon::parse($order_created_at)->diffInMinutes(Carbon::now()) > $these_minutes) ? true : false;
    }

    public function getSellersOfSameCity()
    {
        return Cache::remember('getSellersOfSameCity' . $this->sellerId, Carbon::now()->addDay(), function () {
            return User::getParentAndChildSellersByCity(auth()->user()->city);
        });
    }

    public function getNearBySellers($customer_lat, $customer_lon, $sellers_of_same_city)
    {
        return Cache::remember(
            'getNearBySellers' . $this->sellerId . $customer_lat . $customer_lon,
            Carbon::now()->addDay(),
            function () use ($customer_lat, $customer_lon, $sellers_of_same_city) {
                /* 
                * Add this function when moving to production/staging
                * Bcz this function will not work with "faker" generated 
                * customer lat, lon
                * $nearby_sellers = GoogleMapServices::findDistanceByMakingChunks($customer_lat, $customer_lon, $sellers_of_same_city, 10);
                */
                return GoogleMapServices::findDistanceByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellers_of_same_city, 10);
            }
        );
    }
    /* 
    * CRUD Methods
    */
    public function moveToAnotherSeller($order_id, $order_status, $customer_lat, $customer_lon, $created_at)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellersOfTheSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearby_sellers = $this->getNearBySellers($customer_lat, $customer_lon, $sellersOfTheSameCity);
            $random_index = array_rand($nearby_sellers, 1);
            /* Update sellerId if the current order is older then 2 minutes */
            $moved = false;
            if ($this->isTheOrderOlderThen($this->orderHoldingMinutes, $created_at) && $order_status === 'pending') {
                // Orders::incrementTimesRejected($order_id);
                $moved = Orders::moveToAnotherSeller($order_id, $nearby_sellers[$random_index]['id']);

                info("Order has been move to seller:" . $nearby_sellers[$random_index]['id']);
            }

            /* Operation finished */
            if ($order_status === 'pending') {
                if ($moved) {
                    session()->flash('success', 'Order#' . $order_id . ' has been moved to another seller.');
                } else {
                    session()->flash('warning', 'Soon Order#' . $order_id . ' will be moved to another seller.');
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
            * 1. Use the payment_intent for incremental authorization. 
            * 2. Check the diff between the original price & the current price & then capture a payment.
            */
            $this->selectedOrder = Orders::getById($orderId)->withoutRelations();

            $initialDeliveryCharges = 5;
            $initialTotalAmount = $this->selectedOrder->initial_total + $initialDeliveryCharges;

            $currentDeliveryCharges = 10;
            $currentTotalAmount = $this->selectedOrder->current_total + $currentDeliveryCharges;

            if ($currentTotalAmount > $initialTotalAmount) {
                /* Request incremental authorization */
                $amountToCapture = 100;
            } else {
                $amountToCapture = 50;
            }

            $paymentCaptured = StripeServices::capturePaymentIntent(
                $this->selectedOrder->payment_intent_id,
                $amountToCapture,
                false,
            );
            dd($paymentCaptured);

            // $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::DELIVERED);
            /* Operation finished */
            if (true) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
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
