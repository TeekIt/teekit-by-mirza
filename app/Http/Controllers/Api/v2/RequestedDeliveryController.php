<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\RequestedDelivery\ListRequestedDeliveriesAction;
use App\Enums\OrderByEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestedDelivery\ListRequestedDeliveryRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;

class RequestedDeliveryController extends Controller
{
    public function list(
        ListRequestedDeliveryRequest $request,
        ListRequestedDeliveriesAction $listRequestedDeliveriesAction
    ): JsonResponse {
        $validatedData = (object) $request->validated();
        
        $data = $listRequestedDeliveriesAction->execute(
            orderByEnum: OrderByEnum::DESC,
            creatorId: $validatedData->creatorId,
            columns: [
                'id',
                'creator_id',
                'delivery_provider',
                'delivery_id',
                'pickup_address',
                'dropoff_address',
                'unit_address',
                'receiver_name',
                'receiver_phone',
                'receiver_email',
                'package_transport_type',
                'package_weight',
                'total_cost',
                'created_at',
            ]
        );

        return JsonResponseServices::getPaginatedApiResponse(
            data: $data,
            message: '',
            httpCode: config('constants.HTTP_OK')
        );
    }
}
