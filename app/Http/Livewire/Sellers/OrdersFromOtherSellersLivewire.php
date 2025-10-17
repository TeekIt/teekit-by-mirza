<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\OrdersFromOtherSeller;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\StripeServices;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class OrdersFromOtherSellersLivewire extends Component
{
    public $sellerId;

    public $orderId;

    public int $orderHoldingMinutes = 2;

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
    public function isTheOrderOlderThen(int $theseMinutes, string $orderMovedAt)
    {
        return (Carbon::parse($orderMovedAt)->diffInMinutes(Carbon::now()) > $theseMinutes) ? true : false;
    }

    public function getSellersOfSameCity()
    {
        return Cache::remember(
            'getSellersOfSameCity'.$this->sellerId,
            Carbon::now()->addDay(),
            fn () => User::getParentAndChildSellersByCity(auth()->user()->city)
        );
    }

    public function getNearBySellers($customer_lat, $customer_lon, $sellers_of_same_city)
    {
        return Cache::remember(
            'getNearBySellers'.$this->sellerId.$customer_lat.$customer_lon,
            Carbon::now()->addDay(),
            function () use ($sellers_of_same_city) {
                /*
                * Add this function when moving to production/staging
                * Bcz this function will not work with "faker" generated
                * customer lat, lon
                * $nearby_sellers = GoogleMapServices::findNearByUsersByMakingChunks($customer_lat, $customer_lon, $sellers_of_same_city, 10);
                */
                return GoogleMapServices::findNearByUsersByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellers_of_same_city, 10);
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
    public function moveToAnotherSeller($orderId, $orderStatus, $customerLat, $customerLon, $movedAt)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellersOfSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order-placing buyer */
            $nearbySellers = $this->getNearBySellers($customerLat, $customerLon, $sellersOfSameCity);

            if (empty($nearbySellers)) {
                return $this->noNearBySellers($orderId);
            }

            $randomIndex = array_rand($nearbySellers, 1);
            /* Update sellerId if the current order is older than 2 minutes */
            $moved = false;
            if ($this->isTheOrderOlderThen($this->orderHoldingMinutes, $movedAt) && $orderStatus === 'pending') {
                $moved = OrdersFromOtherSeller::moveToAnotherSeller($orderId, $nearbySellers[$randomIndex]['id']);
                OrdersFromOtherSeller::incrementTimesRejected($orderId);
            }

            /* Operation finished */
            if ($orderStatus === 'pending') {
                if ($moved) {
                    session()->flash('success', 'Order#'.$orderId.' has been moved to another seller');
                } else {
                    session()->flash('warning', 'Soon Order#'.$orderId.' will be moved to another seller');
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

            /* Get sellers who belong to the city of this store owner */
            $sellersOfSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearbySellers = $this->getNearBySellers($customerLat, $customerLon, $sellersOfSameCity);

            if (empty($nearbySellers)) {
                return $this->noNearBySellers($orderId);
            }

            $randomIndex = array_rand($nearbySellers, 1);

            OrdersFromOtherSeller::incrementTimesRejected($orderId);
            $moved = OrdersFromOtherSeller::moveToAnotherSeller($orderId, $nearbySellers[$randomIndex]['id']);

            info('The current order has been sent to seller: '.$nearbySellers[$randomIndex]['id']);
            /* Operation finished */
            sleep(1);

            if ($moved) {
                session()->flash('success', 'Order#'.$orderId.' has been moved to another seller.');
            } else {
                session()->flash('warning', 'Sorry! Order#'.$orderId.' has not been moved to another seller due to some technical error.');
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

    public function readyBySeller($orderFromOtherSeller)
    {
        try {
            /* Perform some operation */
            $updated = OrdersFromOtherSeller::updateOrderStatus(
                $orderFromOtherSeller['id'],
                OrderStatusEnum::READY,
            );

            if ($orderFromOtherSeller['type'] == OrderTypeEnum::SELF_PICKUP->value) {
                $ordersFromOtherSeller = OrdersFromOtherSeller::getById(
                    id: $orderFromOtherSeller['id'],
                    columns: [
                        'id',
                        'created_by_type',
                        'created_by_id',
                        'seller_id',
                        'product_belongs_to_type',
                        'product_belongs_to_id',
                    ]
                );
                EmailServices::sendPickupYourOrderFromOtherSellerMail($ordersFromOtherSeller);
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

    public function cancelOrder($orderId)
    {
        // try {
        //     /* Perform some operation */
        //     $order = OrdersFromOtherSeller::getById(id: $orderId);

        //     StripeServices::refundCustomer($order);

        //     $cancelled = OrdersFromOtherSeller::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);

        //     EmailServices::sendOrderHasBeenCancelledMail($order);
        //     /* Operation finished */
        //     sleep(1);

        //     if ($cancelled) {
        //         session()->flash('success', config('constants.ORDER_CANCELLATION_SUCCESS'));
        //     } else {
        //         session()->flash('error', config('constants.ORDER_CANCELLATION_FAILED'));
        //     }
        // } catch (Exception $error) {
        //     report($error);
        //     session()->flash('error', $error->getMessage());
        // }
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
                'created_by_type',
                'created_by_id',
                'seller_id',
                'parent_order_id',
                'product_belongs_to_type',
                'product_belongs_to_id',
                'product_price',
                'product_qty',
                'initial_total',
                'customer_lat',
                'customer_lon',
                'type',
                'payment_status',
                'order_status',
                'disabled',
                'moved_at',
                'created_at',
            ],
            $this->sellerId,
            'desc'
        );

        return view('livewire.sellers.orders-from-other-sellers-livewire', compact('data'));
    }
}
