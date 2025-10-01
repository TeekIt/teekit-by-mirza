<?php

namespace App\Helpers;

use App\Models\PromoCode;
use App\Models\PromoCodesUsageLimit;
use App\Services\JsonResponseServices;
use App\User;
use Illuminate\Http\JsonResponse;

class PromoCodeHelpers
{
    public static function getTheSellerBelongsToThisPromoCode(PromoCode $promoCode): array
    {
        $seller = User::getUserByID($promoCode->store_id, ['id', 'business_name']);

        return [
            'id' => $seller->id,
            'name' => $seller->business_name,
            'discount' => $promoCode->discount,
        ];
    }

    public static function checkUsageLimitAndReturnResponse(PromoCode $promoCode, object $request): JsonResponse
    {
        if (!PromoCodesUsageLimit::usageLimitReached($promoCode, $request->customerId)) {
            
            // $data['promo_code'] = $promoCode;
            // $data['store'] = ($promoCode->store_id) ? (static::getTheSellerBelongsToThisPromoCode($promoCode)) : (null);

            return JsonResponseServices::getApiResponse(
                $promoCode,
                config('constants.TRUE_STATUS'),
                config('constants.VALID_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.PROMOCODE_REACHED_MAX_LIMIT'),
            config('constants.HTTP_OK')
        );
    }
}
