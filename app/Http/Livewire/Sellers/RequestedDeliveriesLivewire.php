<?php

namespace App\Http\Livewire\Sellers;

use App\Models\RequestedDelivery;
use Livewire\Component;
use Livewire\WithPagination;

class RequestedDeliveriesLivewire extends Component
{
    use WithPagination;

    public
        $sellerId,
        $createdAt = '';

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
    public function resetThisPage()
    {
        $this->reset(['search']);
    }

    public function render()
    {
        $data = RequestedDelivery::getForView(
            'desc',
            $createdAt = '',
            $this->sellerId,
            [
                'id',
                'pickup_address',
                'dropoff_address',
                'unit_address',
                'receiver_name',
                'receiver_phone',
                'receiver_email',
                'package_transport_type',
                'package_weight',
                'created_at',
            ]
        );

        return view('livewire.sellers.requested-deliveries-livewire', compact('data'));
    }
}
