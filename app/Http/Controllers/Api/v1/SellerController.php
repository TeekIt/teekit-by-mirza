<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Services\GoogleMapServices;
use App\Services\JsonResponseServices;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    public function saveStripeAccountId(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
                    ->where(fn (Builder $query) => $query->whereIn('role_id', [UserRoleEnum::SELLER, UserRoleEnum::CHILD_SELLER])),
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
     *
     * @author Muhammad Abdullah Mirza
     */
    public function list(Request $request)
    {
        $validatedData = Validator::make($request->query(), [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'city' => 'required|string',
            'blocked' => 'required|boolean',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $data = Cache::remember(
            'sellersList'.$validatedData->city.$validatedData->lat.$validatedData->lon.$validatedData->blocked,
            now()->addDay(),
            function () use ($validatedData) {
                if ($validatedData->blocked == 1) {
                    $sellers = User::getBlokedParentAndChildSellersByCity(
                        city: $validatedData->city,
                        numberOfRows: 200
                    );
                } else {
                    $sellers = User::getActiveParentAndChildSellersByCity(
                        city: $validatedData->city,
                        numberOfRows: 200
                    );
                }

                if (! $sellers->isEmpty()) {
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
        * Because it will increase the API response time
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
