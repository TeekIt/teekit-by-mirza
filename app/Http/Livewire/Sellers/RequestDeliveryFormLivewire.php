<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Models\RequestedDelivery;
use App\Services\StuartDeliveryServices;
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
        $packageWeight,
        $deliveryCost = 0,
        $currency,
        $disableRequestDeliveryButton = true;

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
    public function resetComponent()
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

    public function calculateDeliveryCost()
    {
        $this->validate();
        // dd($this->pickupAddress);
        try {
            $deliveryCost = StuartDeliveryServices::getDeliveryJobPricing(
                StuartDeliveryServices::getAccessToken(),
                [
                    'job' => [
                        'pickup_at' => now()->addMinutes(10),
                        'assignment_code' => $this->sellerId,
                        'pickups' => [
                            [
                                'address' => $this->pickupAddress,
                                'contact' => [
                                    'firstname' => auth()->user()->name,
                                    'phone' => auth()->user()->business_phone,
                                    'email' => auth()->user()->email,
                                ]
                            ]
                        ],
                        'dropoffs' => [
                            [
                                'package_type' => 'medium',
                                'client_reference' => (string) $this->sellerId,
                                'address' => $this->dropoffAddress,
                                'contact' => [
                                    'firstname' => $this->receiverName,
                                    'phone' => $this->receiverPhone,
                                    'email' => $this->receiverEmail,
                                ]
                            ]
                        ],
                    ]
                ]
            );

            $this->currency = $deliveryCost['currency'] ?? 'GBP';
            $this->deliveryCost = $deliveryCost['amount_with_tax'];
            $this->disableRequestDeliveryButton = false;
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }
    /*
    * CRUD Methods
    */
    public function requestDelivery()
    {
        $this->validate();

        try {

            redirect()->route('stripe.checkout.charge', [
                'totalCharge' => (int) $this->deliveryCost,
            ]);

            // redirect()->route('stripe.checkout.charge', [
            //     'pickupAddress' => $this->pickupAddress,
            //     'dropoffAddress' => $this->dropoffAddress,
            //     'unitAddress' => $this->unitAddress,
            //     'receiverName' => $this->receiverName,
            //     'receiverPhone' => $this->receiverPhone,
            //     'receiverEmail' => $this->receiverEmail,
            //     'packageTransportType' => $this->packageTransportType,
            //     'packageWeight' => $this->packageWeight
            // ]);

            /* Perform some operation */
            // $inserted =  RequestedDelivery::add(
            //     creatorId: $this->sellerId,
            //     pickupAddress: $this->pickupAddress,
            //     dropoffAddress: $this->dropoffAddress,
            //     unitAddress: $this->unitAddress,
            //     receiverName: $this->receiverName,
            //     receiverPhone: $this->receiverPhone,
            //     receiverEmail: $this->receiverEmail,
            //     packageTransportType: PackageTransportTypeEnum::from($this->packageTransportType),
            //     packageWeight: PackageWeightEnum::from($this->packageWeight)
            // );
            // /* Operation finished */
            // sleep(1);

            // if ($inserted) {
            //     session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
            //     $this->resetComponent();
            // } else {
            //     session()->flash('error', config('constants.INSERTION_FAILED'));
            // }
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
