<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodesUsageLimit;
use App\Models\Orders;
use App\Services\JsonResponseServices;
use App\Services\PromoCodeServices;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    /**
     * function will return all promocodes from table
     */
    public function list()
    {
        $promocodes = PromoCode::all();

        $dataIsEmpty = $promocodes->isEmpty();

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $promocodes,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK')
        );
    }

     /**
     * It will fetch a promocode and check all
     * the validation but will not increment the total times
     * a promocode has been used
     */
    public function listByPromoCode(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customerId' => 'required|integer|exists:users,id',
            'promoCode' => 'required|string|max:20|exists:promo_codes,promo_code',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }
        $promoCodesCount = PromoCode::where('promo_code', '=', $request->promoCode)->count();
        if ($promoCodesCount == 1) {
            $expiryDate = PromoCode::where('promo_code', '=', $request->promoCode)->pluck('expiry_dt')->first();
            $currentDate = date('Y-m-d');
            if ($expiryDate < $currentDate) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.EXPIRED_PROMOCODE'),
                    config('constants.HTTP_OK')
                );
            } else {
                $promoCodes = PromoCode::where('promo_code', '=', $request->promoCode)->get();
                if (empty($promoCodes[0]->store_id)) {
                    $promoCodes[0]->store_id = null;
                }
                // below query will pass required data to our helper functions down below to validate
                $promoCodeData = PromoCode::where('promo_code', $request->promoCode)
                    ->first(['id', 'usage_limit', 'store_id', 'discount']);
                /**
                 * This condition will only work if the
                 * Promo code is only valid for a specific order#
                 */
                if (! empty($promoCodes[0]->order_number)) {
                    $userOrdersCount = Orders::where('created_by_id', '=', $request->customerId)->count();
                    if ($promoCodes[0]->order_number == $userOrdersCount + 1) {
                        $data[0]['promo_code'] = $promoCodes[0];
                        $data[1]['promo_codes_usage_limit'] = ($promoCodes[0]->usage_limit) ?
                            PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customerId, $promoCodes[0]->id) :
                            null;
                        // $storeData = PromoCodeServices::getTheSellerBelongsToThisPromoCode($promoCodeData);
                        $data[2]['store'] = ($promoCodeData->store_id) ?
                            PromoCodeServices::getTheSellerBelongsToThisPromoCode($promoCodeData) :
                            null;

                        return JsonResponseServices::getApiResponse(
                            $data,
                            config('constants.TRUE_STATUS'),
                            config('constants.VALID_PROMOCODE'),
                            config('constants.HTTP_OK')
                        );
                    } else {
                        return JsonResponseServices::getApiResponse(
                            [],
                            config('constants.FALSE_STATUS'),
                            'This promo code is only valid for order#'.$promoCodes[0]->order_number,
                            config('constants.HTTP_OK')
                        );
                    }
                }
                $data[0]['promo_code'] = $promoCodes[0];

                $data[1]['promo_codes_usage_limit'] = ($promoCodes[0]->usage_limit) ?
                    PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customerId, $promoCodes[0]->id) :
                    null;

                $data[2]['store'] = ($promoCodeData->store_id) ?
                    PromoCodeServices::getTheSellerBelongsToThisPromoCode($promoCodeData) :
                    null;

                return JsonResponseServices::getApiResponse(
                    $data,
                    config('constants.TRUE_STATUS'),
                    config('constants.VALID_PROMOCODE'),
                    config('constants.HTTP_OK')
                );
            }
        } else {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.INVALID_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }
    }

    /**
     * Validates either the given promo code is correct or not
     * It also checks that either the user is submitting this
     * Promo code for the right order number or not
     * Further it will increment the usage limit
     *
     * @version 1.2.0
     */
    public function validatePromoCode(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customerId' => 'required|integer|exists:users,id',
            'promoCode' => 'required|string|max:20|exists:promo_codes,promo_code',
            'incrementTotalUsed' => ['required', Rule::in(['yes', 'no'])],
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $promoCode = PromoCode::getByPromoCode($validatedData->promoCode, $validatedData->customerId);

        if (PromoCodeServices::isExpired($promoCode)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.EXPIRED_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }
        /**
         * This condition will only work if the
         * Promo code is only valid for a specific order#
         */
        if (! empty($promoCode->order_number)) {
            $userTotalOrders = Orders::getByCreatorId($validatedData->customerId)->count();
            $userCurrentOrderNumber = $userTotalOrders + 1;

            if ($userCurrentOrderNumber != $promoCode->order_number) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'This promo code is only valid for order#'.$promoCode->order_number,
                    config('constants.HTTP_OK')
                );
            }
        }

        if (! empty($promoCode->usage_limit) && $validatedData->incrementTotalUsed === 'yes') {
            return PromoCodeServices::incrementTotalUsedAndReturnResponse(
                $promoCode,
                $request
            );
        }

        return JsonResponseServices::getApiResponse(
            $promoCode,
            config('constants.TRUE_STATUS'),
            config('constants.VALID_PROMOCODE'),
            config('constants.HTTP_OK')
        );
    }
}
