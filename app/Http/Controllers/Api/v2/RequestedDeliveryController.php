<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestedDelivery\ListRequestedDeliveryRequest;
use App\Models\RequestedDelivery;
use App\Services\JsonResponseServices;

class RequestedDeliveryController extends Controller
{
    public function list(ListRequestedDeliveryRequest $request)
    {
        $validatedData = (object) $request->validated();

        $pagination = RequestedDelivery::getForApi(
            orderBy: 'desc',
            creatorId: $validatedData->buyerId,
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
                'created_at',
            ]
        )->toArray();

        $data = $pagination['data'];
        unset($pagination['data']);

        /*
         * Just creating this variable so we don't have to call the "empty()" function again & again
         * Which will obviouly decrease the API response speed
         */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponseExtention(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            'pagination',
            ($dataIsEmpty) ? (object) [] : $pagination,
            config('constants.HTTP_OK')
        );
    }
}
