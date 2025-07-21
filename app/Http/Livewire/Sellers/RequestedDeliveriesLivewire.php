<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Models\RequestedDelivery;
use App\Services\StuartDeliveryServices;
use Exception;
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
        $stuartJobArray = [],
        $search = '';

    protected $paginationTheme = 'bootstrap';
    /* 
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->sellerId = auth()->id();

        if (request()->session()->get('stuartDeliveryDetails')) {
            $this->packageTransportType = request()->session()->get('stuartDeliveryDetails')['packageTransportType'];
            $this->packageWeight = request()->session()->get('stuartDeliveryDetails')['packageWeight'];
            $this->stuartJobArray = request()->session()->get('stuartDeliveryDetails')['jobArray'];

            $this->pickupAddress = $this->stuartJobArray['job']['pickups'][0]['address'];
            $this->dropoffAddress = $this->stuartJobArray['job']['dropoffs'][0]['address'];
            $this->unitAddress = $this->stuartJobArray['job']['dropoffs'][0]['comment'];
            $this->receiverName = $this->stuartJobArray['job']['dropoffs'][0]['contact']['firstname'];
            $this->receiverPhone = $this->stuartJobArray['job']['dropoffs'][0]['contact']['phone'];
            $this->receiverEmail = $this->stuartJobArray['job']['dropoffs'][0]['contact']['email'];

            request()->session()->forget('stuartDeliveryDetails');

            $this->createStuartDelivery();
        }
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
            'packageTransportType',
            'packageWeight',
            'stuartJobArray',
            'search',
        ]);
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

            $inserted =  RequestedDelivery::add(
                creatorId: $this->sellerId,
                deliveryProvider: DeliveryProviderEnum::STUART,
                deliveryId: $response['id'],
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
                $this->resetComponent();
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function cancelDelivery($id)
    {
        dd('Cancelling...' . $id);
    }

    public function renderCancelRequestedDeliveryModal($id)
    {
        $requestedDelivery = RequestedDelivery::find($id);

        $this->requestedDeliveryId    = $requestedDelivery->id;
    }

    public function render()
    {
        $data = RequestedDelivery::getForView(
            orderBy: 'desc',
            createdAt: $this->search,
            creatorId: $this->sellerId,
            columns: [
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
