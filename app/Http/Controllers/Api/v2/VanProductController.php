<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\VanProduct\SyncVanProductAction;
use App\Actions\VanProduct\ListVanProductAction;
use App\Actions\VanProduct\FetchSingleProductAction;
use App\Actions\VanProduct\ListProductsAction;
use App\Actions\VanProduct\SearchProductsAction;
use App\Actions\VanProduct\DashboardStatsAction;
use App\Actions\VanProductUsage\RecordUsageAction;
use App\Actions\VanProductUsage\GetUsageHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\VanProduct\SyncRequest;
use App\Http\Requests\VanProduct\ListByIdRequest;
use App\Http\Requests\VanProduct\ListProductByIdRequest;
use App\Http\Requests\ProductUsageRecord\RecordUsageRequest;
use App\Http\Requests\VanProduct\ListProductsRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class VanProductController extends Controller
{


    /**
     * List all products for the logged-in van with optional filters.
     *
     * @param Request $request
     * @param ListProductsAction $action
     * @return JsonResponse
     */
    public function list(ListProductsRequest $request, ListProductsAction $listProductsAction): JsonResponse
{
    // Execute action to fetch filtered products
    $data = $listProductsAction->execute($request);

    return JsonResponseServices::getApiResponse(
        $data,
        $data->isEmpty() ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
        '', // Empty message
        config('constants.HTTP_OK')
    );
}

    /**
     * Fetch single product details by ID for the logged-in van.
     *
     * @param int $productId
     * @param FetchSingleProductAction $action
     * @return JsonResponse
     */
    public function listById(ListProductByIdRequest $request, FetchSingleProductAction $action): JsonResponse
    {
        
        $data = $action->execute($productId);

        return JsonResponseServices::getApiResponse(
            $data ?? [],
            $data ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Search products by query string for the logged-in van.
     *
     * @param Request $request
     * @param SearchProductsAction $searchProductsAction
     * @return JsonResponse
     */
    public function search(Request $request, SearchProductsAction $action): JsonResponse
{
    $query = $request->query('q', '');

    $data = $action->execute($query);

    return JsonResponseServices::getApiResponse(
        $data,
        $data->isEmpty() ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
        '',
        config('constants.HTTP_OK')
    );
}

    /**
     * Record parts usage by operative
     * POST van/operative/usage/record
     */
    public function recordUsage(RecordUsageRequest $request, RecordUsageAction $recordUsageAction): JsonResponse
    {
        $data = $recordUsageAction->execute($request->validated());

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Get all usage history for the logged-in van
     * GET /van/operative/usage/history
     */
    public function usageHistory(GetUsageHistoryAction $action): JsonResponse
    {
        $data = $action->execute();

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
