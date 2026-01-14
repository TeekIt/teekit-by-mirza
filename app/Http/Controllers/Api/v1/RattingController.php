<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Rattings;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RattingController extends Controller
{
    /** @deprecated This method is deprecated, In case of new requirement we have to re-write this */
    public function add(Request $request)
    {
        $validatedData = Rattings::validator($request);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $ratting = new Rattings;
        $ratting->user_id = Auth::id();
        $ratting->product_id = $request->get('product_id');
        $ratting->ratting = $request->get('ratting');
        $ratting->save();

        return (new ProductsController)->view($request->get('product_id'));
    }

    public function delete($rattingId)
    {
        $ratting = Rattings::find($rattingId);

        if ($ratting) {
            $ratting->delete();

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.ITEM_DELETED'),
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
