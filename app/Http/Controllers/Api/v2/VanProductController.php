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
    public function listProducts(Request $request, ListProductsAction $action): JsonResponse
    {
        // Get logged-in van using auth guard
        $van = auth()->guard('van')->user();
        $vanId = $van->id;

        $filters = [
            'category_id' => $request->query('categoryID'),
            'status' => $request->query('status')
        ];

        // Execute action to fetch filtered products for this van
        $data = $action->execute($filters, $vanId);

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
    public function getProductById(int $productId, FetchSingleProductAction $action): JsonResponse
    {
        // Get logged-in van using auth guard
        $van = auth()->guard('van')->user();
        $vanId = $van->id;

        // Execute action to fetch product belonging to this van
        $data = $action->execute($productId, $vanId);

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
    public function searchProducts(Request $request, SearchProductsAction $searchProductsAction): JsonResponse
    {
        // Get logged-in van using auth guard
        $van = auth()->guard('van')->user();
        $vanId = $van->id;

        // Get search query from request
        $query = $request->query('q', '');

        // Execute action to search products for this van
        $data = $searchProductsAction->execute($query, $vanId);

        return JsonResponseServices::getApiResponse(
            $data,
            $data->isEmpty() ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            '', // Empty message
            config('constants.HTTP_OK')
        );
    }

        /**
     * Record parts usage by operative
     * POST van/operative/usage/record
     */
        public function recordUsage(RecordUsageRequest $request, RecordUsageAction $recordUsageAction)
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
    public function usageHistory(GetUsageHistoryAction $action)
    {
        $van = auth()->guard('van')->user();
        $vanId = $van->id;

        $data = $action->execute($vanId);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '', // empty message
            config('constants.HTTP_OK')
        );
    }

}