<?php

namespace App\Http\Controllers\Api\v2;

use App\Enums\OrderByEnum;
use App\Http\Requests\SuperWallPackage\StoreSuperWallPackageRequest;
use App\Http\Requests\SuperWallPackage\UpdateSuperWallPackageRequest;
use App\Http\Requests\SuperWallPackage\ListSuperWallPackageRequest;
use App\Http\Requests\SuperWallPackage\DeleteSuperWallPackageRequest;
use App\Models\SuperWallPackage;
use App\Http\Controllers\Controller;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;

class SuperWallPackageController extends Controller
{
    public function store(StoreSuperWallPackageRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = SuperWallPackage::add(
            name: $validatedData->name,
            superFastDeliveries: $validatedData->superFastDeliveries,
            unlimitedSuperFastDeliveriesOnOrdersAbove: $validatedData->unlimitedSuperFastDeliveriesOnOrdersAbove,
            currency: $validatedData->currency,
            minDeliveryTimeInMinutes: $validatedData->minDeliveryTimeInMinutes,
            guaranteeDeliveryTimeInMinutes: $validatedData->guaranteeDeliveryTimeInMinutes,
            cashbackPercentage: $validatedData->cashbackPercentage,
            vanDeliveriesDiscountPercentage: $validatedData->vanDeliveriesDiscountPercentage,
            prioritySupport: $validatedData->prioritySupport,
            usersIncluded: $validatedData->usersIncluded,
            freeSameDayDelivery: $validatedData->freeSameDayDelivery
        );

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function list(ListSuperWallPackageRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = SuperWallPackage::getForView(
            orderBy: OrderByEnum::from($validatedData->orderBy)
        );

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function listById(SuperWallPackage $superWallPackageId): JsonResponse
    {
        return JsonResponseServices::getApiResponse(
            $superWallPackageId,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function update(UpdateSuperWallPackageRequest $request): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $updated = SuperWallPackage::updateInfo(
            id: $validatedData->superWallPackageId,
            name: $validatedData->name ?? null,
            superFastDeliveries: $validatedData->superFastDeliveries ?? null,
            unlimitedSuperFastDeliveriesOnOrdersAbove: $validatedData->unlimitedSuperFastDeliveriesOnOrdersAbove ?? null,
            currency: $validatedData->currency ?? null,
            minDeliveryTimeInMinutes: $validatedData->minDeliveryTimeInMinutes ?? null,
            guaranteeDeliveryTimeInMinutes: $validatedData->guaranteeDeliveryTimeInMinutes ?? null,
            cashbackPercentage: $validatedData->cashbackPercentage ?? null,
            vanDeliveriesDiscountPercentage: $validatedData->vanDeliveriesDiscountPercentage ?? null,
            prioritySupport: $validatedData->prioritySupport ?? null,
            usersIncluded: $validatedData->usersIncluded ?? null,
            freeSameDayDelivery: $validatedData->freeSameDayDelivery ?? null
        );

        if ($updated) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.UPDATION_SUCCESS'),
                config('constants.HTTP_OK'),
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.UPDATION_FAILED'),
            config('constants.HTTP_OK')
        );
    }

    public function destroy(DeleteSuperWallPackageRequest $request): JsonResponse
    {               
        $validatedData = (object) $request->validated();

        SuperWallPackage::deletePermanently($validatedData->superWallPackageId);

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.ITEM_DELETED'),
            config('constants.HTTP_OK')
        );
    }
}
