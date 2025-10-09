<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\UserRoleEnum;
use App\Models\Driver;
use App\Pages;
use App\Services\GoogleMapServices;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Services\JsonResponseServices;
use App\Services\WebResponseServices;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class SellerController extends Controller
{
    public function saveStripeAccountId(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
                    ->where(fn(Builder $query) => $query->whereIn('role_id', [UserRoleEnum::SELLER, UserRoleEnum::CHILD_SELLER])),
            ],
            'stripeAccountId' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $user = User::getParentOrChildSellerByEmail(
            email: $validatedData->email,
            columns: ['id']
        );

        $updated = User::updateInfo(
            id: $user->id,
            stripeAccountId: $validatedData->stripeAccountId
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

    /**
     * Listing of all Sellers/Stores within 5 miles
     * @author Muhammad Abdullah Mirza
     */
    public function sellers(Request $request)
    {
        $validatedData = Validator::make($request->query(), [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'city' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();
    
        $data = Cache::remember(
            'sellers' . $validatedData->city . $validatedData->lat . $validatedData->lon,
            now()->addDay(),
            function () use ($validatedData) {
                $sellers = User::getParentAndChildSellersByCity(city: $validatedData->city, numberOfRows: 200);
                
                if (!$sellers->isEmpty()) {
                    return GoogleMapServices::findNearByUsersByMakingChunks(
                        lat: $validatedData->lat,
                        lon: $validatedData->lon,
                        users: $sellers
                    );
                }
            }
        );
        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Which will obviouly decrease the API response speed
        */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_STORES_FOUND') : '',
            config('constants.HTTP_OK'),
        );
    }
}
