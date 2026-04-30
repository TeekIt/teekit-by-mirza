<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\VanOperativeProductUsage\ListVanOperativeProductUsageAction;
use App\Actions\VanOperativeProductUsage\StoreVanOperativeProductUsageAction;
use App\Actions\VanProductUsage\RecordUsageAction;
use App\Actions\VanProductUsage\GetUsageHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductUsageRecord\RecordUsageRequest;
use App\Http\Requests\VanOperativeProductUsage\StoreVanOperativeProductUsageRequest;
use App\Models\VanOperativeProductUsage;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;

class VanOperativeProductUsageController extends Controller
{
    public function store(
        StoreVanOperativeProductUsageRequest $storeVanOperativeProductUsageRequest,
        StoreVanOperativeProductUsageAction $storeVanOperativeProductUsageAction
    ): JsonResponse {
        $validatedData = $storeVanOperativeProductUsageRequest->validated();

        $data = $storeVanOperativeProductUsageAction->execute($validatedData);

        return JsonResponseServices::getApiResponse(
            ($data instanceof VanOperativeProductUsage) ? $data : [],
            config('constants.TRUE_STATUS'),
            ($data instanceof VanOperativeProductUsage) ? '' : $data,
            config('constants.HTTP_OK')
        );
    }

    public function list(ListVanOperativeProductUsageAction $listVanOperativeProductUsageAction): JsonResponse
    {
        $data = $listVanOperativeProductUsageAction->execute();

        return JsonResponseServices::getPaginatedApiResponse(
            $data,
            '',
            config('constants.HTTP_OK')
        );
    }
}
