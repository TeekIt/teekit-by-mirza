<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\VanProduct\SyncVanProductAction;
use App\Actions\VanProduct\ListVanProductAction;
use App\Actions\VanProduct\FetchSingleProductAction;
use App\Actions\VanProduct\ListProductsAction;
use App\Actions\VanProduct\SearchProductsAction;
use App\Actions\VanProduct\DashboardStatsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\VanProduct\SyncRequest;
use App\Http\Requests\VanProduct\ListByIdRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanProductController extends Controller
{
    /**
     * Sync van products with the server
     *
     * @param SyncRequest $request
     * @param SyncVanProductAction $syncVanProductAction
     * @return JsonResponse
     */
    public function sync(SyncRequest $request, SyncVanProductAction $syncVanProductAction): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = $syncVanProductAction->execute($validatedData->van_id);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'Data synced successfully',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Fetch details of a van by van_id
     *
     * @param ListByIdRequest $request
     * @param SyncVanProductAction $syncVanProductAction
     * @return JsonResponse
     */
    public function listById(ListByIdRequest $request, SyncVanProductAction $syncVanProductAction): JsonResponse
    {
        $vanId = $request->validated()['van_id'];

        $data = $syncVanProductAction->executeById($vanId);

        return JsonResponseServices::getApiResponse(
            $data ?: [],
            !empty($data) ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
            !empty($data) ? 'Van details fetched successfully' : 'Van not found',
            config('constants.HTTP_OK')
        );
    }

    /**
     * List van products with optional filters
     *
     * Filters: category_id, status (active/inactive/critical/out_of_stock)
     *
     * @param Request $request
     * @param ListProductsAction $action
     * @return JsonResponse
     */
    public function listProducts(Request $request, ListProductsAction $action): JsonResponse
    {
        $filters = $request->only(['category_id', 'status']);

        $products = $action->execute($filters);

        return JsonResponseServices::getApiResponse(
            $products,
            $products->isEmpty() ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            $products->isEmpty() ? 'No products found' : 'Products fetched successfully',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Fetch a single product by product ID
     *
     * @param int $productId
     * @param FetchSingleProductAction $fetchSingleProductAction
     * @return JsonResponse
     */
    public function getProductById(int $productId, FetchSingleProductAction $fetchSingleProductAction): JsonResponse
    {
        $data = $fetchSingleProductAction->execute($productId);

        return JsonResponseServices::getApiResponse(
            $data,
            !empty($data) ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
            !empty($data) ? 'Product fetched successfully' : 'Product not found',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Search products by query string
     *
     * @param Request $request
     * @param SearchProductsAction $searchProductsAction
     * @return JsonResponse
     */
    public function searchProducts(Request $request, SearchProductsAction $searchProductsAction): JsonResponse
    {
        $query = $request->query('q', '');
        $data = $searchProductsAction->execute($query);

        return JsonResponseServices::getApiResponse(
            $data,
            $data->isEmpty() ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            $data->isEmpty() ? 'No products found for search query' : 'Products fetched successfully',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Fetch dashboard statistics for a specific van
     *
     * @param Request $request
     * @param DashboardStatsAction $action
     * @return JsonResponse
     */
    public function dashboardStats(Request $request, DashboardStatsAction $action): JsonResponse
    {
        $vanId = $request->input('van_id');

        if (!$vanId) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                'van_id is required',
                config('constants.HTTP_BAD_REQUEST')
            );
        }

        $data = $action->execute($vanId);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'Dashboard stats fetched successfully',
            config('constants.HTTP_OK')
        );
    }
}