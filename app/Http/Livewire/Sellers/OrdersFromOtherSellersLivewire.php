<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Models\OrdersFromOtherSeller;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class OrdersFromOtherSellersLivewire extends Component
{
    public
        $seller_id,
        $order_holding_minutes = 2;

    /* 
    * Lifecycle Hooks
    */
    public function mount()
    {
        $this->seller_id = auth()->id();
    }
    /* 
    * Helpers
    */
    public function isTheOrderOlderThen(int $these_minutes, string $order_created_at)
    {
        return (Carbon::parse($order_created_at)->diffInMinutes(Carbon::now()) > $these_minutes) ? true : false;
    }

    public function getSellersOfSameCity()
    {
        return Cache::remember(
            'getSellersOfSameCity' . $this->sellerId,
            Carbon::now()->addDay(),
            fn () => User::getParentAndChildSellersByCity(auth()->user()->city)
        );
    }

    public function getNearBySellers($customer_lat, $customer_lon, $sellers_of_same_city)
    {
        return Cache::remember(
            'getNearBySellers' . $this->seller_id . $customer_lat . $customer_lon,
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
    public function moveToAnotherSeller($orderId, $orderStatus, $customerLat, $customerLon, $createdAt)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellersOfSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order-placing buyer */
            $nearbySellers = $this->getNearBySellers($customerLat, $customerLon, $sellersOfSameCity);
            $randomIndex = array_rand($nearbySellers, 1);
            /* Update sellerId if the current order is older than 2 minutes */
            $moved = false;
            if ($this->isTheOrderOlderThen($this->orderHoldingMinutes, $createdAt) && $orderStatus === 'pending') {
                OrdersFromOtherSeller::incrementTimesRejected($orderId);
                $moved = OrdersFromOtherSeller::moveToAnotherSeller($orderId, $nearbySellers[$randomIndex]['id']);
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

    public function rejectedBySeller($order_id, $customer_lat, $customer_lon)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellers_of_same_city = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearby_sellers = $this->getNearBySellers($customer_lat, $customer_lon, $sellers_of_same_city);
            $random_index = array_rand($nearby_sellers, 1);

            OrdersFromOtherSeller::incrementTimesRejected($order_id);
            $moved = OrdersFromOtherSeller::moveToAnotherSeller($order_id, $nearby_sellers[$random_index]['id']);

            /* Operation finished */
            if ($moved) {
                session()->flash('success', 'Order#' . $order_id . ' has been moved to another seller.');
            } else {
                session()->flash('warning', 'Sorry! Order#' . $order_id . ' has not been moved to another seller due to some technical error.');
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function acceptedBySeller($order_from_other_seller)
    {
        try {
            /* Perform some operation */
            OrdersFromOtherSeller::isViewed($order_from_other_seller['id']);
            $updated = OrdersFromOtherSeller::updateOrderStatus(
                $order_from_other_seller['id'],
                OrderStatusEnum::ACCEPTED,
            );
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

    public function readyBySeller($order_from_other_seller)
    {
        try {
            /* Perform some operation */
            $updated = OrdersFromOtherSeller::updateOrderStatus(
                $order_from_other_seller['id'],
                OrderStatusEnum::READY,
            );
            if ($order_from_other_seller['type'] == 'self-pickup') {
                $order_details = OrdersFromOtherSeller::getById([
                    'id',
                    'customer_id',
                    'seller_id',
                    'product_id'
                ], $order_from_other_seller['id']);
                EmailServices::sendPickupYourOrderFromOtherSellerMail($order_details);
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

    public function deliveredBySeller($order_from_other_seller)
    {
        try {
            /* Perform some operation */
            $updated = OrdersFromOtherSeller::updateOrderStatus(
                $order_from_other_seller['id'],
                OrderStatusEnum::DELIVERED
            );
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

    /* 
    * IMPORTANT NOTE!!!
    * completeBySeller function is not created yet bcz the order will
    * Only be marked as completed if the delivery boy marks it as complete 
    * As soon as the delivery boy marks it as complete the called API 
    * Will add the order amount into the seller's wallet 
    */

    public function render()
    {
        $data = OrdersFromOtherSeller::getForView(
            [
                'id',
                'customer_id',
                'seller_id',
                'product_id',
                'product_price',
                'product_qty',
                'initial_total',
                'customer_lat',
                'customer_lon',
                'type',
                'payment_status',
                'order_status',
                'created_at'
            ],
            $this->seller_id,
            'desc'
        );

        return view('livewire.sellers.orders-from-other-sellers-livewire', ['data' => $data]);
    }
}
