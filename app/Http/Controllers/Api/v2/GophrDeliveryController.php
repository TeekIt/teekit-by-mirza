<?php

namespace App\Http\Controllers\Api\v2;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gophr\AddGophrJobRequest;
use App\Models\RequestedDelivery;
use App\Models\User;
use App\Services\GophrDeliveryServices;
use App\Services\JsonResponseServices;
use App\Services\UUIDServices;
use Illuminate\Http\JsonResponse;

class GophrDeliveryController extends Controller
{
    public function store(AddGophrJobRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $parcelData = [
            'parcel_external_id' => UUIDServices::generateUUID(),
            'parcel_reference_number' => UUIDServices::generateUUID(),
            'parcel_description' => $validatedData->productDetails ?? 'Please pickup your order ASAP',
            'width' => 1,
            'length' => 1,
            'height' => 1,
            'weight' => 1,
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
                dropoffPersonName: User::getAuthUser()->name,
                dropoffEmail: User::getAuthUser()->email,
                dropoffMobileNumber: User::getAuthUser()->country_code . User::getAuthUser()->phone
            )
        );

        RequestedDelivery::add(
            creatorId: User::getAuthUser()->id,
            deliveryProvider: DeliveryProviderEnum::GOPHR,
            deliveryId: $response->data->job_id,
            pickupAddress: $validatedData->pickupAddress,
            dropoffAddress: $validatedData->dropoffAddress,
            unitAddress: $validatedData->unitAddress,
            receiverName: User::getAuthUser()->name,
            receiverPhone: User::getAuthUser()->country_code . User::getAuthUser()->phone,
            receiverEmail: User::getAuthUser()->email,
            packageTransportType: PackageTransportTypeEnum::from($validatedData->packageTransportType),
            packageWeight: PackageWeightEnum::from($validatedData->packageWeight),
            totalCost: $validatedData->totalCost
        );

        return JsonResponseServices::getApiResponse(
            $response->data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function getDeliveryJobPricing(AddGophrJobRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $parcelData = [
            'parcel_external_id' => UUIDServices::generateUUID(),
            'parcel_reference_number' => UUIDServices::generateUUID(),
            'parcel_description' => 'Please pickup your order ASAP',
            'width' => 1,
            'length' => 1,
            'height' => 1,
            'weight' => 1,
        ];

        $response = GophrDeliveryServices::getJobPricing(
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
                dropoffPersonName: User::getAuthUser()->name,
                dropoffEmail: User::getAuthUser()->email,
                dropoffMobileNumber: User::getAuthUser()->country_code . User::getAuthUser()->phone
            )
        );

        return JsonResponseServices::getApiResponse(
            $response->data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function trackDeliveryJob(string $jobId): JsonResponse
    {
        return JsonResponseServices::getApiResponse(
            GophrDeliveryServices::getJob($jobId)->data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
