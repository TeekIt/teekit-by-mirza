<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Http\Requests\Gophr\AddGophrJobRequest;
use App\Models\RequestedDelivery;
use App\Services\GophrDeliveryServices;
use App\Services\JsonResponseServices;
use App\Services\UUIDServices;

class GophrDeliveryController extends Controller
{
    public function createDeliveryJob(AddGophrJobRequest $request)
    {
        $validatedData = (object) $request->validated();

        $parcelData = [
            'parcel_external_id' => UUIDServices::generateUUID(),
            'parcel_reference_number' => UUIDServices::generateUUID(),
            'parcel_description' => 'Please pickup your order ASAP',
            'width' => 0,
            'length' => 0,
            'height' => 0,
            'weight' => 0,
        ];

        $response = GophrDeliveryServices::createJob(
            GophrDeliveryServices::prepareJobArray(
                externalId: UUIDServices::generateUUID(),
                pickupAddress: $validatedData->pickupAddress,
                pickupCity: $validatedData->pickupCity,
                pickupPostcode: $validatedData->pickupPostcode,
                pickupLat: $validatedData->pickupLat,
                pickupLon: $validatedData->pickupLon,
                pickupPersonName: $validatedData->senderName,
                pickupMobileNumber: $validatedData->senderPhone,
                parcelExternalId: $parcelData['parcel_external_id'],
                parcelReferenceNumber: $parcelData['parcel_reference_number'],
                parcelDescription: $parcelData['parcel_description'],
                width: $parcelData['width'],
                length: $parcelData['length'],
                height: $parcelData['height'],
                weight: $parcelData['weight'],
                dropoffAddress: $validatedData->dropoffAddress,
                dropoffCity: $validatedData->dropOffCity,
                dropoffPostcode: $validatedData->dropOffPostCode,
                dropoffLat: $validatedData->dropoffLat,
                dropoffLon: $validatedData->dropoffLon,
                dropoffPersonName: auth()->user()->name,
                dropoffEmail: auth()->user()->email,
                dropoffMobileNumber: auth()->user()->country_code . auth()->user()->phone
            )
        );

        RequestedDelivery::add(
            creatorId: auth()->id(),
            deliveryProvider: DeliveryProviderEnum::GOPHR,
            deliveryId: $response->data->job_id,
            pickupAddress: $validatedData->pickupAddress,
            dropoffAddress: $validatedData->dropoffAddress,
            unitAddress: $validatedData->unitAddress,
            receiverName: auth()->user()->name,
            receiverPhone: auth()->user()->country_code . auth()->user()->phone,
            receiverEmail: auth()->user()->email,
            packageTransportType: PackageTransportTypeEnum::from($validatedData->packageTransportType),
            packageWeight: PackageWeightEnum::from($validatedData->packageWeight)
        );

        return JsonResponseServices::getApiResponse(
            $response->data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function getDeliveryJobPricing(AddGophrJobRequest $request)
    {
        $validatedData = (object) $request->validated();

        $parcelData = [
            'parcel_external_id' => UUIDServices::generateUUID(),
            'parcel_reference_number' => UUIDServices::generateUUID(),
            'parcel_description' => 'Please pickup your order ASAP',
            'width' => 0,
            'length' => 0,
            'height' => 0,
            'weight' => 0,
        ];

        $response = GophrDeliveryServices::createJob(
            GophrDeliveryServices::prepareJobArray(
                externalId: UUIDServices::generateUUID(),
                pickupAddress: $validatedData->pickupAddress,
                pickupCity: $validatedData->pickupCity,
                pickupPostcode: $validatedData->pickupPostcode,
                pickupLat: $validatedData->pickupLat,
                pickupLon: $validatedData->pickupLon,
                pickupPersonName: $validatedData->senderName,
                pickupMobileNumber: $validatedData->senderPhone,
                parcelExternalId: $parcelData['parcel_external_id'],
                parcelReferenceNumber: $parcelData['parcel_reference_number'],
                parcelDescription: $parcelData['parcel_description'],
                width: $parcelData['width'],
                length: $parcelData['length'],
                height: $parcelData['height'],
                weight: $parcelData['weight'],
                dropoffAddress: $validatedData->dropoffAddress,
                dropoffCity: $validatedData->dropOffCity,
                dropoffPostcode: $validatedData->dropOffPostCode,
                dropoffLat: $validatedData->dropoffLat,
                dropoffLon: $validatedData->dropoffLon,
                dropoffPersonName: auth()->user()->name,
                dropoffEmail: auth()->user()->email,
                dropoffMobileNumber: auth()->user()->country_code . auth()->user()->phone
            )
        );

        return JsonResponseServices::getApiResponse(
            $response,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function trackDeliveryJob(string $jobId)
    {
        return JsonResponseServices::getApiResponse(
            GophrDeliveryServices::getJob($jobId),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
