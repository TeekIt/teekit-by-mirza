<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\PromoCodesUsageLimit;
use App\Services\JsonResponseServices;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PromoCodeServices
{
    public static function isExpired(PromoCode $promoCode): bool
    {
        return ($promoCode->expiry_dt < date('Y-m-d')) ?? false;
    }

    public static function usageLimitReached(PromoCodesUsageLimit $promoCodesUsageLimit, PromoCode $promoCode): bool
    {
        return ($promoCodesUsageLimit->total_used === $promoCode->usage_limit) ?? false;
    }

    public static function getTheSellerBelongsToThisPromoCode(PromoCode $promoCode): array
    {
        $seller = User::getUserByID($promoCode->store_id, ['id', 'business_name']);

        return [
            'id' => $seller->id,
            'name' => $seller->business_name,
            'discount' => $promoCode->discount,
        ];
    }

    public static function incrementTotalUsedAndReturnResponse(PromoCode $promoCode, Request $request): JsonResponse
    {
        $promoCodesUsageLimit = PromoCodesUsageLimit::getByPromoCodeIdAndCustomerId($promoCode->id, $request->customerId);

        if (empty($promoCodesUsageLimit)) {
            PromoCodesUsageLimit::add($promoCode->id, $request->customerId);

            return JsonResponseServices::getApiResponse(
                PromoCode::getByPromoCode($promoCode->promo_code, $request->customerId),
                config('constants.TRUE_STATUS'),
                config('constants.VALID_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }

        if (self::usageLimitReached($promoCodesUsageLimit, $promoCode)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.PROMOCODE_REACHED_MAX_LIMIT'),
                config('constants.HTTP_OK')
            );
        }

        $promoCodesUsageLimit->increment('total_used');
        // $data['promo_code'] = $promoCode;
        // $data['store'] = ($promoCode->store_id) ? (static::getTheSellerBelongsToThisPromoCode($promoCode)) : (null);

        return JsonResponseServices::getApiResponse(
            PromoCode::getByPromoCode($promoCode->promo_code, $request->customerId),
            config('constants.TRUE_STATUS'),
            config('constants.VALID_PROMOCODE'),
            config('constants.HTTP_OK')
        );
    }
}
