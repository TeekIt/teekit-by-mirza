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
        $createdAt,
        $requestedDeliveryId,
        $pickupAddress,
        $dropoffAddress,
        $unitAddress,
        $receiverName,
        $receiverPhone,
        $receiverEmail,
        $packageTransportType,
        $packageWeight,
        $search = '';

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

    public function closeModal($modalId)
    {
        $this->dispatchBrowserEvent('close-modal', ['id' => $modalId]);
    }
    /* 
     * CRUD Methods
     */
    public function cancelDelivery($id)
    {
        dd('Cancelling...' . $id);
    }

    public function renderCancelRequestedDeliveryModal($id)
    {
        $requestedDelivery = RequestedDelivery::find($id);
        $this->requestedDeliveryId    = $requestedDelivery->id;
        // $this->pickupAddress          = $requestedDelivery->pickup_address;
        // $this->dropoffAddress         = $requestedDelivery->dropoff_address;
        // $this->unitAddress            = $requestedDelivery->unit_address;
        // $this->receiverName           = $requestedDelivery->receiver_name;
        // $this->receiverPhone          = $requestedDelivery->receiver_phone;
        // $this->receiverEmail          = $requestedDelivery->receiver_email;
        // $this->packageTransportType   = $requestedDelivery->package_transport_type;
        // $this->packageWeight          = $requestedDelivery->package_weight;
    }

    public function render()
    {
        $data = RequestedDelivery::getForView(
            'desc',
            $this->search,
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
