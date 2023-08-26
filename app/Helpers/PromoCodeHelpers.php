<?php

namespace App\Helpers;

use App\Models\PromoCodesUsageLimit;
use App\Services\JsonResponseCustom;
use App\User;
use Illuminate\Http\Request;

class PromoCodeHelpers
{
    public static function ifPromoCodeBelongsToStore($promo_code_data)
    {
        $store = User::where('id', $promo_code_data->store_id)->first();
        if (empty($store)) {
            return false;
        } else {
            $data = [
                'id' => $store->id,
                'name' => $store->business_name,
                'discount' => $promo_code_data->discount,
            ];
            return $data;
        }
    }

    public static function checkUsageLimit(object $promo_codes, object $promo_code_data, object $request)
    {
        if (PromoCodesUsageLimit::promoCodeUsageLimit($promo_code_data, $request->user_id) == 1) {
            $data[0]['promo_code'] = $promo_codes[0];
            $store_data = static::ifPromoCodeBelongsToStore($promo_code_data);
            $data[1]['store'] = ($store_data) ? ($store_data) : (NULL);
            return JsonResponseCustom::getApiResponse(
                $data,
                true,
                config('constants.VALID_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        } else {
            return JsonResponseCustom::getApiResponse(
                [],
                false,
                config('constants.MAX_LIMIT'),
                config('constants.HTTP_OK')
            );
        }
    }
}
