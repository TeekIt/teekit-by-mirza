<?php

namespace App\Http\Livewire\Sellers;

use App\Models\OrdersFromOtherSeller;
use App\Services\GoogleMapServices;
use App\User;
use Livewire\Component;

class OrdersFromOtherSellersLivewire extends Component
{
    public
        $seller_id,
        $initialMinutes,
        $initialSeconds;

    public function mount()
    {
        $this->seller_id = auth()->id();
    }

    public function checkIfOrderFromOtherStoreIsOlderThen($minutes){
        $order_from_other_seller->created_at->diffInMinutes(\Carbon\Carbon::now()) > 2
    }

    public function moveToAnotherStore($order_id){
        // dd('moved');
        // Update $seller_id against this $order_id
        $sellers = User::getParentAndChildSellersByCity(auth()->user()->city);
        $nearby_sellers = GoogleMapServices::findDistanceByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellers, 25);
        $random_index = array_rand($nearby_sellers, 1);
        dd($nearby_sellers[$random_index]['id']);
        // Update seller_id if the current order is older then 2 minutes
        
        OrdersFromOtherSeller::updateSellerId($order_id, $nearby_sellers[$random_index]['id']);
    }

    public function render()
    {
        $data = OrdersFromOtherSeller::getOrdersFromOtherSellersForView(
            [
                'id',
                'customer_id',
                'seller_id',
                'product_id',
                'product_price',
                'product_qty',
                'order_total',
                'type',
                'payment_status',
                'order_status',
                'created_at'
            ],
            $this->seller_id,
            'desc'
        );
        // dd($data);
        return view('livewire.sellers.orders-from-other-sellers-livewire', ['data' => $data]);
    }
}
