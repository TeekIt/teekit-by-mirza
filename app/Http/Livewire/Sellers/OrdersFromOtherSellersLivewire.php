<?php

namespace App\Http\Livewire\Sellers;

use App\Models\OrdersFromOtherSeller;
use Livewire\Component;

class OrdersFromOtherSellersLivewire extends Component
{
    public $seller_id;

    public function mount()
    {
        $this->seller_id = auth()->id();
    }

    public function render()
    {
        $data = OrdersFromOtherSeller::getOrdersFromOtherSellersForView(
            [
                'id',
                'customer_id',
                'seller_id',
                'order_total',
                'type',
                'payment_status',
                'order_status',
                'created_at'
            ],
            $this->seller_id,
            'desc'
        );
        dd($data);
        return view('livewire.sellers.orders-from-other-sellers-livewire', ['data' => $data]);
    }
}
