<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Enums\StuartPackageTypeEnum;
use App\Services\CompanyStandardsServices;
use App\Services\GophrDeliveryServices;
use App\Services\JsonParsingServices;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Exception;
use Illuminate\Support\Facades\Log;

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
            'unitAddress' => 'required|string',
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
                'pickup_at' => StuartDeliveryServices::getStandardPickUpTime(),
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
                        'package_type' => StuartDeliveryServices::mapPkgWeightWithStuartPkgType($this->packageWeight),
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

    public function prepareGophrJobArray()
    {
        $parcelData = [
            'parcel_external_id' => UUIDServices::generateUUID(),
            'parcel_reference_number' => UUIDServices::generateUUID(),
            'parcel_description' => 'Please pickup your order ASAP',
            'width' => 0,
            'length' => 0,
            'height' => 0,
            'weight' => 0,
        ];

        return [
            'is_confirmed' => 1,
            'external_id' => UUIDServices::generateUUID(),
            'pickups' => [
                [
                    'pickup_address1' => $this->pickupAddress,
                    'pickup_city' => auth()->user()->city,
                    'pickup_postcode' => auth()->user()->postcode,
                    'pickup_country_code' => 'GB',
                    'pickup_location_lat' => auth()->user()->lat,
                    'pickup_location_lng' => auth()->user()->lon,
                    'pickup_person_name' => auth()->user()->name,
                    'pickup_mobile_number' => auth()->user()->business_phone,
                    'parcels' => [
                        $parcelData
                    ]
                ]
            ],
            'dropoffs' => [
                [
                    'dropoff_address1' => $this->dropoffAddress,
                    'dropoff_city' => auth()->user()->city,
                    'dropoff_postcode' => $this->unitAddress,
                    'dropoff_country_code' => 'GB',
                    // 'dropoff_location_lat' => ,
                    // 'dropoff_location_lng' => ,
                    'dropoff_person_name' => $this->receiverName,
                    'dropoff_email' => $this->receiverEmail,
                    'dropoff_mobile_number' => $this->receiverPhone,
                    'dropoff_instructions' => 'Make the delivery possible ASAP',
                    'dropoff_deadline' => CompanyStandardsServices::getStandardDeliveryDeadline()->toIso8601String(),
                    'parcels' => [
                        $parcelData
                    ]
                ]
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

                $this->currency = $response['currency'];
                $this->deliveryCharges = $response['amount'];
                $this->serviceCharges = CompanyStandardsServices::$standardServiceCharges;
                $this->tax = $response['amount_with_tax'] - $response['amount'];
                $this->totalCost = $this->calculateTotalCost();
            }

            if ($this->deliveryServiceName === DeliveryProviderEnum::GOPHR->value) {
                $this->requestDeliveryButtonTxt = 'Request Gophr Delivery';

                $response = GophrDeliveryServices::getJobPricing(
                    $this->prepareGophrJobArray()
                );
                $response = JsonParsingServices::convertStdClassToArray($response->data);

                $this->currency = $response['price_net']['currency'];
                $this->deliveryCharges = $response['price_net']['amount'];
                $this->serviceCharges = CompanyStandardsServices::$standardServiceCharges;
                $this->tax = $response['price_gross']['amount'] - $response['price_net']['amount'];
                $this->totalCost = $this->calculateTotalCost();
            }

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
            if ($this->deliveryServiceName === DeliveryProviderEnum::STUART->value) {
                request()->session()->put('stuartDeliveryDetails', [
                    'jobArray' => $this->prepareStuartJobArray(),
                    'packageTransportType' => $this->packageTransportType,
                    'packageWeight' => $this->packageWeight,
                ]);
            }

            if ($this->deliveryServiceName === DeliveryProviderEnum::GOPHR->value) {
                request()->session()->put('gophrDeliveryDetails', [
                    'jobArray' => $this->prepareGophrJobArray(),
                    'packageTransportType' => $this->packageTransportType,
                    'packageWeight' => $this->packageWeight,
                ]);
            }

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
