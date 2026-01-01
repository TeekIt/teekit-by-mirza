<?php

namespace App\Livewire\Sellers;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Models\RequestedDelivery;
use App\Services\GophrDeliveryServices;
use App\Services\JsonParsingServices;
use App\Services\StuartDeliveryServices;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class RequestedDeliveriesLivewire extends Component
{
    use WithPagination;

    public $sellerId;

    public $createdAt;

    public $requestedDeliveryId;

    public $pickupAddress;

    public $dropoffAddress;

    public $unitAddress;

    public $productDetails;

    public $receiverName;

    public $receiverPhone;

    public $receiverEmail;

    public $totalCost;

    public $selectedDeliveryDetails;

    public $deliveryServiceName;

    public $stuartJobArray = [];

    public $gophrJobArray = [];

    public $search = '';

    public PackageTransportTypeEnum $packageTransportType = PackageTransportTypeEnum::SMALL_VAN;

    public PackageWeightEnum $packageWeight = PackageWeightEnum::SMALL;

    public DeliveryProviderEnum $deliveryProvider = DeliveryProviderEnum::STUART;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    /*
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->sellerId = auth()->id();

        if (request()->session()->get('stuartDeliveryDetails')) {
            $this->deliveryProvider = DeliveryProviderEnum::STUART;

            $this->packageTransportType = PackageTransportTypeEnum::from(request()->session()->get('stuartDeliveryDetails')['packageTransportType']);
            $this->packageWeight = PackageWeightEnum::from(request()->session()->get('stuartDeliveryDetails')['packageWeight']);
            $this->totalCost = request()->session()->get('stuartDeliveryDetails')['totalCost'];
            $this->stuartJobArray = request()->session()->get('stuartDeliveryDetails')['jobArray'];

            $this->pickupAddress = $this->stuartJobArray['job']['pickups'][0]['address'];
            $this->dropoffAddress = $this->stuartJobArray['job']['dropoffs'][0]['address'];
            $this->unitAddress = $this->stuartJobArray['dropoffUnitAddress'];
            $this->productDetails = $this->stuartJobArray['job']['dropoffs'][0]['comment'];
            $this->receiverName = $this->stuartJobArray['job']['dropoffs'][0]['contact']['firstname'];
            $this->receiverPhone = $this->stuartJobArray['job']['dropoffs'][0]['contact']['phone'];
            $this->receiverEmail = $this->stuartJobArray['job']['dropoffs'][0]['contact']['email'];

            request()->session()->forget('stuartDeliveryDetails');

            $this->createStuartDelivery();
        }

        if (request()->session()->get('gophrDeliveryDetails')) {
            $this->deliveryProvider = DeliveryProviderEnum::GOPHR;

            $this->packageTransportType = PackageTransportTypeEnum::from(request()->session()->get('gophrDeliveryDetails')['packageTransportType']);
            $this->packageWeight = PackageWeightEnum::from(request()->session()->get('gophrDeliveryDetails')['packageWeight']);
            $this->totalCost = request()->session()->get('gophrDeliveryDetails')['totalCost'];
            $this->gophrJobArray = request()->session()->get('gophrDeliveryDetails')['jobArray'];

            $this->pickupAddress = $this->gophrJobArray['pickups'][0]['pickup_address1'];
            $this->dropoffAddress = $this->gophrJobArray['dropoffs'][0]['dropoff_address1'];
            $this->unitAddress = $this->gophrJobArray['dropoffs'][0]['dropoff_postcode'];
            $this->productDetails = $this->gophrJobArray['dropoffs'][0]['parcel_description'];
            $this->receiverName = $this->gophrJobArray['dropoffs'][0]['dropoff_person_name'];
            $this->receiverPhone = $this->gophrJobArray['dropoffs'][0]['dropoff_mobile_number'];
            $this->receiverEmail = $this->gophrJobArray['dropoffs'][0]['dropoff_email'];

            request()->session()->forget('gophrDeliveryDetails');

            $this->createGophrDelivery();
        }
    }

    /*
    * Helpers
    */
    public function resetComponent()
    {
        $this->resetValidation();

        $this->reset([
            'createdAt',
            'requestedDeliveryId',
            'pickupAddress',
            'dropoffAddress',
            'unitAddress',
            'receiverName',
            'receiverPhone',
            'receiverEmail',
            'totalCost',
            'packageTransportType',
            'packageWeight',
            'deliveryProvider',
            'selectedDeliveryDetails',
            'deliveryServiceName',
            'stuartJobArray',
            'gophrJobArray',
            'search',
        ]);
    }

    public function resetThisPage()
    {
        $this->reset(['search']);
    }

    public function showModal($modalId)
    {
        $this->dispatch('show-modal', ['id' => $modalId]);
    }

    public function closeModal($modalId)
    {
        $this->dispatch('close-modal', ['id' => $modalId]);
    }

    public function renderTrackDeliveryModal($deliveryId, $deliveryServiceName)
    {
        $this->deliveryServiceName = $deliveryServiceName;

        if ($deliveryServiceName === DeliveryProviderEnum::STUART->value) {
            $this->selectedDeliveryDetails = StuartDeliveryServices::getJob($deliveryId);
            $this->showModal('trackStuartDeliveryModal');
        }

        if ($deliveryServiceName === DeliveryProviderEnum::GOPHR->value) {
            $this->selectedDeliveryDetails = JsonParsingServices::convertStdClassToArray(GophrDeliveryServices::getJob($deliveryId));
            $this->showModal('trackGophrDeliveryModal');
        }
    }

    /*
     * CRUD Methods
     */
    public function createStuartDelivery()
    {
        try {
            /* Perform some operation */
            $response = StuartDeliveryServices::createJob(
                $this->stuartJobArray
            );
            $this->requestedDeliveryId = $response['id'];

            $inserted = $this->addDeliveryDetailsIntoDatabase();
            /* Operation finished */
            sleep(1);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
                $this->resetComponent();
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function createGophrDelivery()
    {
        try {
            /* Perform some operation */
            $response = GophrDeliveryServices::createJob(
                $this->gophrJobArray
            );
            $this->requestedDeliveryId = $response->data->job_id;

            $inserted = $this->addDeliveryDetailsIntoDatabase();
            /* Operation finished */
            sleep(1);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
                $this->resetComponent();
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function addDeliveryDetailsIntoDatabase()
    {
        return RequestedDelivery::add(
            creatorId: $this->sellerId,
            deliveryProvider: $this->deliveryProvider,
            deliveryId: $this->requestedDeliveryId,
            pickupAddress: $this->pickupAddress,
            dropoffAddress: $this->dropoffAddress,
            unitAddress: $this->unitAddress,
            receiverName: $this->receiverName,
            receiverPhone: $this->receiverPhone,
            receiverEmail: $this->receiverEmail,
            packageTransportType: $this->packageTransportType,
            packageWeight: $this->packageWeight,
            totalCost: $this->totalCost
        );
    }

    // public function cancelDelivery($id)
    // {
    //     dd('Cancelling...' . $id);
    // }

    // public function renderCancelRequestedDeliveryModal($id)
    // {
    //     $requestedDelivery = RequestedDelivery::find($id);

    //     $this->requestedDeliveryId    = $requestedDelivery->id;
    // }

    public function render()
    {
        $data = RequestedDelivery::getForView(
            orderBy: 'desc',
            createdAt: $this->search,
            creatorId: $this->sellerId,
            columns: [
                'id',
                'delivery_provider',
                'delivery_id',
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
