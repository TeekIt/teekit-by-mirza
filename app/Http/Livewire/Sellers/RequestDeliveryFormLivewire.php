<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Models\RequestedDelivery;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Exception;


class RequestDeliveryFormLivewire extends Component
{
    public
        $sellerId,
        $pickupAddress,
        $dropoffAddress,
        $unitAddress,
        $receiverName,
        $receiverPhone,
        $receiverEmail,
        $packageTransportType,
        $packageWeight;

    protected function rules()
    {
        return [
            'pickupAddress' => 'required|string',
            'dropoffAddress' => 'required|string',
            'unitAddress' => 'nullable|string',
            'receiverName' => 'required|string',
            'receiverPhone' => 'required|numeric',
            'receiverEmail' => 'required|email',
            'packageTransportType' => ['required', Rule::enum(PackageTransportTypeEnum::class)],
            'packageWeight' => ['required', Rule::enum(PackageWeightEnum::class)],
        ];
    }
    /* 
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->sellerId = auth()->id();
        $this->pickupAddress = auth()->user()->full_address;
    }
    /* 
     * Helpers
     */
    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'dropoffAddress',
            'unitAddress',
            'receiverName',
            'receiverPhone',
            'receiverEmail',
            'packageTransportType',
            'packageWeight',
        ]);
    }

    public function changePackageWeight()
    {
        switch ($this->packageTransportType) {
            case PackageTransportTypeEnum::MOPED->value:
                $this->packageWeight = PackageWeightEnum::SMALL->value;
                break;
            case PackageTransportTypeEnum::CAR_BOOT->value:
                $this->packageWeight = PackageWeightEnum::MEDIUM->value;
                break;
            case PackageTransportTypeEnum::SMALL_VAN->value:
                $this->packageWeight = PackageWeightEnum::LARGE->value;
                break;
            case PackageTransportTypeEnum::BIG_VAN->value:
                $this->packageWeight = PackageWeightEnum::EXTRA_LARGE->value;
        }
    }
    /*
    * CRUD Methods
    */
    public function requestDelivery()
    {
        $this->validate();

        try {
            /* Perform some operation */
            $inserted =  RequestedDelivery::add(
                creatorId: $this->sellerId,
                pickupAddress: $this->pickupAddress,
                dropoffAddress: $this->dropoffAddress,
                unitAddress: $this->unitAddress,
                receiverName: $this->receiverName,
                receiverPhone: $this->receiverPhone,
                receiverEmail: $this->receiverEmail,
                packageTransportType: PackageTransportTypeEnum::from($this->packageTransportType),
                packageWeight: PackageWeightEnum::from($this->packageWeight)
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
        return view('livewire.sellers.request-delivery-form-livewire');
    }
}
