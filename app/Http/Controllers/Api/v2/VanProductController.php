<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\VanProduct\SyncVanProductAction;
use App\Actions\VanProduct\ListVanProductAction;
use App\Actions\VanProduct\FetchSingleProductAction;
use App\Actions\VanProduct\ListVanProductsAction;
use App\Actions\VanProduct\SearchProductsAction;
use App\Actions\VanProduct\DashboardStatsAction;
use App\Actions\VanProductUsage\RecordUsageAction;
use App\Actions\VanProductUsage\GetUsageHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\VanProduct\SyncRequest;
use App\Http\Requests\VanProduct\ListByIdRequest;
use App\Http\Requests\VanProduct\SearchProductsRequest;
use App\Http\Requests\VanProduct\ListProductByIdRequest;
use App\Http\Requests\ProductUsageRecord\RecordUsageRequest;
use App\Http\Requests\VanProduct\ListVanProductByIdRequest;
use App\Http\Requests\VanProduct\ListVanProductsRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class VanProductController extends Controller
{
    public function list(ListVanProductsRequest $listVanProductsRequest, ListVanProductsAction $listVanProductsAction): JsonResponse
    {
        $validatedData = $listVanProductsRequest->validated();

        $data = $listVanProductsAction->execute($validatedData);

        return JsonResponseServices::getPaginatedApiResponse(
            $data,
            '',
            config('constants.HTTP_OK')
        );
    }

    public function listById(
        ListVanProductByIdRequest $listVanProductByIdRequest,
        ListVanProductsAction $listVanProductsAction
    ): JsonResponse {
        $validatedData = $listVanProductByIdRequest->validated();

        $data = $listVanProductsAction->execute($validatedData);

        return JsonResponseServices::getApiResponse(
            $data ?? [],
            $data ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function search(Request $request, SearchProductsAction $searchProductsAction): JsonResponse
    {
        $query = $request->query('q', '');

        $data = $searchProductsAction->execute($query);

        return JsonResponseServices::getPaginatedApiResponse(
            $data,
            '',
            config('constants.HTTP_OK')
        );
    }
}
