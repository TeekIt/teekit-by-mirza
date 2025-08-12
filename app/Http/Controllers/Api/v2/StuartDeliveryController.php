<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Http\Requests\Stuart\AddStuartJobRequest;
use App\Models\RequestedDelivery;
use App\Services\JsonResponseServices;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;

class StuartDeliveryController extends Controller
{
    public function createDeliveryJob(AddStuartJobRequest $request)
    {
        $validatedData = (object) $request->validated();

        $assignmentCode = UUIDServices::generateUUID();
        $response = StuartDeliveryServices::createJob([
            'job' => [
                'pickup_at' => StuartDeliveryServices::getStandardPickUpTime(),
                'assignment_code' => $assignmentCode,
                'pickups' => [
                    [
                        'address' => $validatedData->pickupAddress,
                        'contact' => [
                            'firstname' => $validatedData->senderName,
                            'phone' => $validatedData->senderPhone,
                            'email' => $validatedData->senderEmail,
                        ]
                    ]
                ],
                'dropoffs' => [
                    [
                        'package_type' => StuartDeliveryServices::mapPkgWeightWithStuartPkgType(
                            PackageWeightEnum::from($validatedData->packageWeight)
                        ),
                        'client_reference' => $assignmentCode,
                        'address' => $validatedData->dropoffAddress,
                        'comment' => $validatedData->unitAddress,
                        'contact' => [
                            'firstname' => auth()->user()->name,
                            'phone' => auth()->user()->country_code . auth()->user()->phone,
                            'email' => auth()->user()->email,
                        ]
                    ]
                ]
            ]
        ]);

        RequestedDelivery::add(
            creatorId: auth()->id(),
            deliveryProvider: DeliveryProviderEnum::STUART,
            deliveryId: $response['id'],
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
            $response,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function getDeliveryJobPricing(AddStuartJobRequest $request)
    {
        $validatedData = (object) $request->validated();

        $assignmentCode = UUIDServices::generateUUID();
        $response = StuartDeliveryServices::getJobPricing([
            'job' => [
                'pickup_at' => StuartDeliveryServices::getStandardPickUpTime(),
                'assignment_code' => $assignmentCode,
                'pickups' => [
                    [
                        'address' => $validatedData->pickupAddress,
                        'contact' => [
                            'firstname' => $validatedData->senderName,
                            'phone' => $validatedData->senderPhone,
                            'email' => $validatedData->senderEmail,
                        ]
                    ]
                ],
                'dropoffs' => [
                    [
                        'package_type' => StuartDeliveryServices::mapPkgWeightWithStuartPkgType(
                            PackageWeightEnum::from($validatedData->packageWeight)
                        ),
                        'client_reference' => $assignmentCode,
                        'address' => $validatedData->dropoffAddress,
                        'comment' => $validatedData->unitAddress,
                        'contact' => [
                            'firstname' => auth()->user()->name,
                            'phone' => auth()->user()->country_code . auth()->user()->phone,
                            'email' => auth()->user()->email,
                        ]
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
