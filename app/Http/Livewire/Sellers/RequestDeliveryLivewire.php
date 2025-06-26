<?php

namespace App\Http\Livewire\Sellers;

use App\Models\RequestedDelivery;
use Exception;
use Livewire\Component;

class RequestDeliveryLivewire extends Component
{
    public
        $sellerId,
        $pickupAddress,
        $dropoffAddress,
        $receiverName,
        $receiverPhone,
        $packageSize,
        $packageWeight;
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
            'pickupAddress',
            'dropoffAddress',
            'receiverName',
            'receiverPhone',
            'packageSize',
            'packageWeight',
        ]);
    }
    /*
    * CRUD Methods
    */
    public function requestDelivery()
    {
        try {
            /* Perform some operation */
            $inserted =  RequestedDelivery::add(
                creatorId: $this->sellerId,
                pickupAddress: $this->pickupAddress,
                dropoffAddress: $this->dropoffAddress,
                receiverName: $this->receiverName,
                receiverPhone: $this->receiverPhone,
                packageSize: $this->packageSize,
                packageWeight: $this->packageWeight
            );
            /* Operation finished */
            sleep(1);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
                $this->resetModal();
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sellers.request-delivery-livewire');
    }
}
