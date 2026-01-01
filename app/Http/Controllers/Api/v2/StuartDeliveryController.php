<?php

namespace App\Http\Controllers\Api\v2;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stuart\AddStuartJobRequest;
use App\Models\RequestedDelivery;
use App\Services\CompanyStandardsServices;
use App\Services\JsonResponseServices;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;
use Illuminate\Http\JsonResponse;

class StuartDeliveryController extends Controller
{
    public function createDeliveryJob(AddStuartJobRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $response = StuartDeliveryServices::createJob(
            StuartDeliveryServices::prepareJobArray(
                pickupAt: CompanyStandardsServices::getStandardPickUpTime()->toDateTimeString(),
                assignmentCode: UUIDServices::generateUUID(),
                pickupAddress: $validatedData->pickupAddress,
                senderName: $validatedData->senderName,
                senderPhone: $validatedData->senderPhone,
                senderEmail: $validatedData->senderEmail,
                packageType: StuartDeliveryServices::mapPkgWeightWithStuartPkgType(
                    PackageWeightEnum::from($validatedData->packageWeight)
                ),
                dropoffAddress: $validatedData->dropoffAddress,
                dropoffUnitAddress: $validatedData->unitAddress,
                comment: $validatedData->productDetails ?? 'Please pickup your order ASAP',
                receiverName: auth()->user()->name,
                receiverPhone: auth()->user()->country_code . auth()->user()->phone,
                receiverEmail: auth()->user()->email
            )
        );

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
            packageWeight: PackageWeightEnum::from($validatedData->packageWeight),
            totalCost: $validatedData->totalCost
        );

        return JsonResponseServices::getApiResponse(
            $response,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function getDeliveryJobPricing(AddStuartJobRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $response = StuartDeliveryServices::getJobPricing(
            StuartDeliveryServices::prepareJobArray(
                pickupAt: CompanyStandardsServices::getStandardPickUpTime()->toDateTimeString(),
                assignmentCode: UUIDServices::generateUUID(),
                pickupAddress: $validatedData->pickupAddress,
                senderName: $validatedData->senderName,
                senderPhone: $validatedData->senderPhone,
                senderEmail: $validatedData->senderEmail,
                packageType: StuartDeliveryServices::mapPkgWeightWithStuartPkgType(
                    PackageWeightEnum::from($validatedData->packageWeight)
                ),
                dropoffAddress: $validatedData->dropoffAddress,
                unitAddress: $validatedData->unitAddress,
                receiverName: auth()->user()->name,
                receiverPhone: auth()->user()->country_code . auth()->user()->phone,
                receiverEmail: auth()->user()->email
            )
        );

        return JsonResponseServices::getApiResponse(
            $response,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function trackDeliveryJob(string $jobId): JsonResponse
    {
        return JsonResponseServices::getApiResponse(
            StuartDeliveryServices::getJob($jobId),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
