<?php

namespace App\Livewire\Sellers;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Models\User;
use App\Services\CompanyStandardsServices;
use App\Services\GophrDeliveryServices;
use App\Services\JsonParsingServices;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;
use Exception;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RequestDeliveryFormLivewire extends Component
{
    public $sellerId;

    public $pickupAddress;

    public $dropoffAddress;

    public $dropoffUnitAddress;

    public $dropoffLat;

    public $dropoffLon;

    public $receiverName;

    public $receiverPhone;

    public $receiverEmail;

    public $packageTransportType;

    public $packageWeight;

    public $productDetails;

    public $deliveryCharges = 0;

    public $serviceCharges = 0;

    public $tax = 0;

    public $totalCost = 0;

    public $currency;

    public $disableRequestDeliveryButton = true;

    public $requestDeliveryButtonTxt = 'Request';

    public $deliveryServiceName;

    protected function rules()
    {
        return [
            'pickupAddress' => 'required|string',
            'dropoffAddress' => 'required|string',
            'dropoffUnitAddress' => 'required|string',
            'dropoffLat' => 'required|numeric',
            'dropoffLon' => 'required|numeric',
            'receiverName' => 'required|string',
            'receiverPhone' => 'required|numeric',
            'receiverEmail' => 'required|email',
            'packageTransportType' => ['required', Rule::enum(PackageTransportTypeEnum::class)],
            'packageWeight' => ['required', Rule::enum(PackageWeightEnum::class)],
            'productDetails' => 'nullable|string',
        ];
    }

    /*
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->sellerId = User::getAuthUser()->id;
        $this->pickupAddress = User::getAuthUser()->full_address;
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

    public function updateLivewireProperties(
        $dropoffLat = null,
        $dropoffLon = null,
        $dropoffAddress = null,
        $dropoffUnitAddress = null,
        $pickupAddress = null
    ) {
        if ($dropoffLat !== null) {
            $this->dropoffLat = $dropoffLat;
        }

        if ($dropoffLon !== null) {
            $this->dropoffLon = $dropoffLon;
        }

        if ($dropoffAddress !== null) {
            $this->dropoffAddress = $dropoffAddress;
        }

        if ($dropoffUnitAddress !== null) {
            $this->dropoffUnitAddress = $dropoffUnitAddress;
        }

        if ($pickupAddress !== null) {
            $this->pickupAddress = $pickupAddress;
        }

        $this->inputFieldChanged();
    }

    public function prepareStuartJobArray()
    {
        return StuartDeliveryServices::prepareJobArray(
            pickupAt: CompanyStandardsServices::getStandardPickUpTime()->toDateTimeString(),
            assignmentCode: UUIDServices::generateUUID(),
            pickupAddress: $this->pickupAddress,
            senderName: User::getAuthUser()->name,
            senderPhone: User::getAuthUser()->business_phone,
            senderEmail: User::getAuthUser()->email,
            packageType: StuartDeliveryServices::mapPkgWeightWithStuartPkgType(PackageWeightEnum::from($this->packageWeight)),
            dropoffAddress: $this->dropoffAddress,
            dropoffUnitAddress: $this->dropoffUnitAddress,
            comment: $this->productDetails ?? 'Please pickup your order ASAP',
            receiverName: $this->receiverName,
            receiverPhone: $this->receiverPhone,
            receiverEmail: $this->receiverEmail
        );
    }

    public function prepareGophrJobArray()
    {
        return GophrDeliveryServices::prepareJobArray(
            externalId: UUIDServices::generateUUID(),
            pickupAddress: $this->pickupAddress,
            pickupCity: User::getAuthUser()->city,
            pickupPostcode: User::getAuthUser()->postcode,
            pickupLat: User::getAuthUser()->lat,
            pickupLon: User::getAuthUser()->lon,
            pickupPersonName: User::getAuthUser()->name,
            pickupMobileNumber: User::getAuthUser()->business_phone,
            parcelExternalId: UUIDServices::generateUUID(),
            parcelReferenceNumber: UUIDServices::generateUUID(),
            parcelDescription: $this->productDetails ?? 'Please pickup your order ASAP',
            width: 1,
            length: 1,
            height: 1,
            weight: 1,
            dropoffAddress: $this->dropoffAddress,
            dropoffCity: User::getAuthUser()->city,
            dropoffPostcode: $this->dropoffUnitAddress,
            dropoffLat: $this->dropoffLat,
            dropoffLon: $this->dropoffLon,
            dropoffPersonName: $this->receiverName,
            dropoffEmail: $this->receiverEmail,
            dropoffMobileNumber: $this->receiverPhone
        );
    }

    public function calculateTotalCost()
    {
        return round($this->deliveryCharges + $this->serviceCharges + $this->tax);
    }

    public function setDeliveryServiceName($deliveryServiceName)
    {
        if (! in_array($deliveryServiceName, array_column(DeliveryProviderEnum::cases(), 'value'))) {
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
                $this->serviceCharges = CompanyStandardsServices::STANDARD_SERVICE_CHARGES;
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
                $this->serviceCharges = CompanyStandardsServices::STANDARD_SERVICE_CHARGES;
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
                    'jobArray' => array_merge($this->prepareStuartJobArray(), [
                        'dropoffUnitAddress' => $this->dropoffUnitAddress,
                    ]),
                    'packageTransportType' => $this->packageTransportType,
                    'packageWeight' => $this->packageWeight,
                    'totalCost' => $this->totalCost,
                ]);
            }

            if ($this->deliveryServiceName === DeliveryProviderEnum::GOPHR->value) {
                request()->session()->put('gophrDeliveryDetails', [
                    'jobArray' => $this->prepareGophrJobArray(),
                    'packageTransportType' => $this->packageTransportType,
                    'packageWeight' => $this->packageWeight,
                    'totalCost' => $this->totalCost,
                ]);
            }

            redirect()->route('stripe.requested.delivery.checkout.form', [
                'totalCharge' => $this->totalCost,
                'productName' => uniqid('requested-delivery-'),
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
