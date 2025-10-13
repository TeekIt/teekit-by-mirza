<?php

namespace App\Services;

use App\Enums\UserRoleEnum;

final class SellerServices
{
    /**
     * Fetch seller information w.r.t ID
     *
     * @author Muhammad Abdullah Mirza
     */
    public static function getStandardSellerInfo(object $seller, ?array $mapApiResult = null)
    {
        $data = [
            'id' => $seller->id,
            'name' => $seller->name,
            'email' => $seller->email,
            'business_name' => $seller->business_name,
            'business_hours' => $seller->business_hours,
            'full_address' => $seller->full_address,
            'unit_address' => $seller->unit_address,
            'country' => $seller->country,
            'state' => $seller->state,
            'city' => $seller->city,
            'postcode' => $seller->postcode,
            'lat' => $seller->lat,
            'lon' => $seller->lon,
            'user_img' => $seller->user_img,
            'pending_withdraw' => $seller->pending_withdraw,
            'total_withdraw' => $seller->total_withdraw,
            'parent_store_id' => $seller->parent_store_id,
            'is_active' => $seller->is_active,
            'is_online' => $seller->is_online,
            'roles' => ($seller->role_id == UserRoleEnum::SELLER->value) ? ['sellers'] : ['child_sellers'],
            'stripe_account_id' => $seller->stripe_account_id,
        ];

        if (! is_null($mapApiResult)) {
            $data['distance'] = $mapApiResult['distance'];
            $data['duration'] = $mapApiResult['duration'];
        }

        return $data;
    }
}
