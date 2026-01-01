<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Qty;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QtyController extends Controller
{
    /**
     * It will return a specific product's qty
     *
     * @version 1.0.0
     */
    public function getById(Request $request)
    {
        $validatedData = Validator::make($request->route()->parameters(), [
            'store_id' => 'required|integer',
            'prod_id' => 'required|integer',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $qty = Qty::where('seller_id', '=', $request->store_id)
            ->where('product_id', '=', $request->prod_id)
            ->get();

        if (! is_null($qty)) {
            return JsonResponseServices::getApiResponse(
                $qty,
                config('constants.TRUE_STATUS'),
                '',
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.NO_RECORD'),
            config('constants.HTTP_OK')
        );
    }
}
