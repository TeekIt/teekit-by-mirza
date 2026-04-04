<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\JsonResponseServices;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class BuyerController extends Controller
{
    /**
     * It will update user details
     * via given id
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.1.0
     */
    public function updateBuyer(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'fName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'lName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'password' => 'nullable|string|min:8|max:50',
            'countryCode' => 'required|string|max:4',
            'phone' => 'required|string|max:13',
            'fullAddress' => 'required|string',
            'unitAddress' => 'nullable|string',
            'country' => 'required|string|max:70',
            'state' => 'required|string|max:70',
            'city' => 'required|string|max:70',
            'postcode' => 'nullable|string|max:11',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $updated = User::updateInfo(
            id: User::getAuthUser()->id,
            name: $request->fName,
            lName: $request->lName,
            // phone: $request->countryCode . $request->phone,
            phone: $request->phone,
            fullAddress: $request->fullAddress,
            unitAddress: $request->unitAddress,
            country: $request->country,
            state: $request->state,
            city: $request->city,
            postcode: $request->postcode,
            lat: $request->lat,
            lon: $request->lon,
            password: $request->password,
        );

        if ($updated) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.UPDATION_SUCCESS'),
                config('constants.HTTP_OK'),
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.UPDATION_FAILED'),
            config('constants.HTTP_OK')
        );
    }
}
