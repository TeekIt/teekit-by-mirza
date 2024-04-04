<?php

namespace App\Http\Livewire\Sellers;

use App\Models\OrdersFromOtherSeller;
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
        $initialMinutes,
        $initialSeconds,
        $nearby_sellers;

    public function mount()
    {
        $this->seller_id = auth()->id();
    }

    public function checkIfOrderFromOtherStoreIsOlderThen($minutes)
    {
        // $order_from_other_seller->created_at->diffInMinutes(\Carbon\Carbon::now()) > 2
    }

    public function moveToAnotherSeller($order_id, $customer_lat, $customer_lon, $created_at)
    {
        try {
            /* Perform some operation */

            /* Get sellers who belongs to the city of this store owner */
            $sellers_of_same_city = Cache::remember('moveToAnotherSeller' . $this->seller_id, Carbon::now()->addHour(), function () {
                return User::getParentAndChildSellersByCity(auth()->user()->city);
            });
            // Get sellers who are nearby to the order placing buyer
            /* 
            * Add this function when moving to production/staging
            * Bcz this function will not work with "faker" generated 
            * customer lat, lon
            * $nearby_sellers = GoogleMapServices::findDistanceByMakingChunks($customer_lat, $customer_lon, $sellers, 10);
            */
            $nearby_sellers = GoogleMapServices::findDistanceByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellers_of_same_city, 10);
            // dd($nearby_sellers);
            $random_index = array_rand($nearby_sellers, 1);
            // dd($nearby_sellers[$random_index]['id']);

            /* Update seller_id if the current order if it is older then 2 minutes */
            $moved = false;
            if (Carbon::parse($created_at)->diffInMinutes(Carbon::now()) > 2) {
                OrdersFromOtherSeller::incrementTimesRejected($order_id);
                $moved = OrdersFromOtherSeller::moveToAnotherSeller($order_id, $nearby_sellers[$random_index]['id']);
            }

            /* Operation finished */
            if ($moved) {
                session()->flash('success', 'Order#' . $order_id . ' has been moved to another seller.');
            } else {
                session()->flash('warning', 'Soon Order#' . $order_id . ' will be moved to another seller.');
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

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
                'order_total',
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
