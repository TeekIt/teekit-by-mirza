<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Enums\StuartPackageTypeEnum;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;
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
        $deliveryCharges = 0,
        $serviceCharges = 0,
        $tax = 0,
        $totalCost = 0,
        $currency,
        $disableRequestDeliveryButton = true,
        $requestDeliveryButtonTxt = 'Request',
        $deliveryServiceName;

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
    public function changePackageWeight()
    {
        $this->inputFieldChanged();

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

    public function mapPkgWeightWithStuartPkgType()
    {
        switch ($this->packageWeight) {
            case PackageWeightEnum::SMALL->value:
                return StuartPackageTypeEnum::SMALL->value;
            case PackageWeightEnum::MEDIUM->value:
                return StuartPackageTypeEnum::MEDIUM->value;
            case PackageWeightEnum::LARGE->value:
                return StuartPackageTypeEnum::LARGE->value;
            case PackageWeightEnum::EXTRA_LARGE->value:
                return StuartPackageTypeEnum::EXTRA_LARGE->value;
        }
    }

    public function inputFieldChanged()
    {
        $this->requestDeliveryButtonTxt = 'Request';
        $this->disableRequestDeliveryButton = true;
        $this->deliveryCharges = 0;
        $this->serviceCharges = 0;
        $this->tax = 0;
        $this->totalCost = 0;
        $this->currency = '';
    }

    public function prepareStuartJobArray()
    {
        $assignmentCode = UUIDServices::generateUUID();

        return [
            'job' => [
                'pickup_at' => now()->addMinutes(15),
                'assignment_code' => $assignmentCode,
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
                        'package_type' => $this->mapPkgWeightWithStuartPkgType(),
                        'client_reference' => $assignmentCode,
                        'address' => $this->dropoffAddress,
                        'comment' => $this->unitAddress,
                        'contact' => [
                            'firstname' => $this->receiverName,
                            'phone' => $this->receiverPhone,
                            'email' => $this->receiverEmail,
                        ]
                    ]
                ],
            ]
        ];
    }

    public function calculateTotalCost()
    {
        return round($this->deliveryCharges + $this->serviceCharges + $this->tax);
    }

    public function setDeliveryServiceName($deliveryServiceName)
    {
        if (!in_array($deliveryServiceName, array_column(DeliveryProviderEnum::cases(), 'value'))) {
            throw new Exception('Invalid delivery provider');
        }

        $this->deliveryServiceName = $deliveryServiceName;
    }

    public function calculateDeliveryCost($deliveryServiceName)
    {
        $this->validate();

        try {
            $this->setDeliveryServiceName($deliveryServiceName);

            if ($this->deliveryServiceName === DeliveryProviderEnum::STUART->value) {
                $this->requestDeliveryButtonTxt = 'Request Staurt Delivery';

                $response = StuartDeliveryServices::getJobPricing(
                    $this->prepareStuartJobArray()
                );
            }

            $this->currency = $response['currency'];
            $this->deliveryCharges = $response['amount'];
            $this->serviceCharges = 1.99;
            $this->tax = $response['amount_with_tax'] - $response['amount'];
            $this->totalCost = $this->calculateTotalCost();
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
        $this->disableRequestDeliveryButton = true;

        try {
            request()->session()->put('stuartDeliveryDetails', [
                'jobArray' => $this->prepareStuartJobArray(),
                'packageTransportType' => $this->packageTransportType,
                'packageWeight' => $this->packageWeight,
            ]);

            redirect()->route('stripe.requested.delivery.checkout.form', [
                'totalCharge' => $this->totalCost,
                'productName' => uniqid('requested-delivery-')
            ]);
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
