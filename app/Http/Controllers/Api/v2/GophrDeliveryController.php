<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Http\Requests\Gophr\AddGophrJobRequest;
use App\Models\RequestedDelivery;
use App\Services\CompanyStandardsServices;
use App\Services\GophrDeliveryServices;
use App\Services\JsonResponseServices;
use App\Services\StuartDeliveryServices;
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

        $response = GophrDeliveryServices::createJob([
            'is_confirmed' => 1,
            'external_id' => UUIDServices::generateUUID(),
            'pickups' => [
                [
                    'pickup_address1' => $validatedData->pickupAddress,
                    'pickup_city' => $validatedData->pickupCity,
                    'pickup_postcode' => $validatedData->pickupPostcode,
                    'pickup_country_code' => 'GB',
                    'pickup_location_lat' => $validatedData->pickupLat,
                    'pickup_location_lng' => $validatedData->pickupLon,
                    'pickup_person_name' => $validatedData->senderName,
                    'pickup_mobile_number' => $validatedData->senderPhone,
                    'parcels' => [
                        $parcelData
                    ]
                ]
            ],
            'dropoffs' => [
                [
                    'dropoff_address1' => $validatedData->dropoffAddress,
                    'dropoff_city' => $validatedData->dropOffCity,
                    'dropoff_postcode' => $validatedData->dropOffPostCode,
                    'dropoff_country_code' => 'GB',
                    'dropoff_location_lat' => $validatedData->dropoffLat,
                    'dropoff_location_lng' => $validatedData->dropoffLon,
                    'dropoff_person_name' => auth()->user()->name,
                    'dropoff_email' => auth()->user()->email,
                    'dropoff_mobile_number' => auth()->user()->country_code . auth()->user()->phone,
                    'dropoff_instructions' => 'Make the delivery possible ASAP',
                    'dropoff_deadline' => CompanyStandardsServices::getStandardDeliveryDeadline()->toIso8601String(),
                    'parcels' => [
                        $parcelData
                    ]
                ]
            ]
        ]);

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

        $response = GophrDeliveryServices::getJobPricing([
            'is_confirmed' => 1,
            'external_id' => UUIDServices::generateUUID(),
            'pickups' => [
                [
                    'pickup_address1' => $validatedData->pickupAddress,
                    'pickup_city' => $validatedData->pickupCity,
                    'pickup_postcode' => $validatedData->pickupPostcode,
                    'pickup_country_code' => 'GB',
                    'pickup_location_lat' => $validatedData->pickupLat,
                    'pickup_location_lng' => $validatedData->pickupLon,
                    'pickup_person_name' => $validatedData->senderName,
                    'pickup_mobile_number' => $validatedData->senderPhone,
                    'parcels' => [
                        $parcelData
                    ]
                ]
            ],
            'dropoffs' => [
                [
                    'dropoff_address1' => $validatedData->dropoffAddress,
                    'dropoff_city' => $validatedData->dropOffCity,
                    'dropoff_postcode' => $validatedData->dropOffPostCode,
                    'dropoff_country_code' => 'GB',
                    'dropoff_location_lat' => $validatedData->dropoffLat,
                    'dropoff_location_lng' => $validatedData->dropoffLon,
                    'dropoff_person_name' => auth()->user()->name,
                    'dropoff_email' => auth()->user()->email,
                    'dropoff_mobile_number' => auth()->user()->country_code . auth()->user()->phone,
                    'dropoff_instructions' => 'Make the delivery possible ASAP',
                    'dropoff_deadline' => CompanyStandardsServices::getStandardDeliveryDeadline()->toIso8601String(),
                    'parcels' => [
                        $parcelData
                    ]
                ]
            ]
        ]);

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
            StuartDeliveryServices::getJob($jobId),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
