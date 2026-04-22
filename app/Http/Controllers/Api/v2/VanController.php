<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\Van\ListVanAction;
use App\Actions\Van\ListVanStatsAction;
use App\Actions\Van\LoginVanAction;
use App\Actions\Van\FetchRecentActivityAction;
use App\Actions\Van\FetchDashboardStatsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Van\ListVanRequest;
use App\Http\Requests\Van\LoginVanRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;

class VanController extends Controller
{
    public function loginVan(LoginVanRequest $request, LoginVanAction $loginVanAction): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = $loginVanAction->execute($validatedData->userName, $validatedData->password);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'You have logged in successfully',
            config('constants.HTTP_OK')
        );
    }

    public function listById(ListVanRequest $request, ListVanAction $listVanAction): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = $listVanAction->execute(['id' => $validatedData->id]);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function dashboardStats(FetchDashboardStatsAction $fetchDashboardStatsAction): JsonResponse
    {
        $data = $fetchDashboardStatsAction->execute(auth()->guard('van')->user()->id);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function recentActivities(FetchRecentActivityAction $fetchRecentActivityAction): JsonResponse
        {
        $columns = ['product_name', 'job_reference', 'quantity', 'updated_at'];

        $data = $fetchRecentActivityAction->execute(
            auth()->guard('van')->user()->id,
            $columns
        );

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
