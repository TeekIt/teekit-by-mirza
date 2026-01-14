<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCodesUsageLimit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promo_codes_usage_limit';

    protected $fillable = [
        'promo_code_id',
        'customer_id',
        'total_used',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * Relations
     */
    //

    /**
     * Helpers
     */
    public static function getByPromoCodeIdAndCustomerId(int $promoCodeId, int $customerId): ?PromoCodesUsageLimit
    {
        return self::where('promo_code_id', '=', $promoCodeId)
            ->where('customer_id', '=', $customerId)
            ->first();
    }

    public static function add(int $promoCodeId, int $customerId): PromoCodesUsageLimit
    {
        $promoCodesUsageLimit = new self;
        $promoCodesUsageLimit->promo_code_id = $promoCodeId;
        $promoCodesUsageLimit->customer_id = $customerId;
        $promoCodesUsageLimit->total_used = 1;
        $promoCodesUsageLimit->save();

        return $promoCodesUsageLimit;
    }

    // public static function addOrIncrementTotalUsed(PromoCode $promoCode, int $customerId): PromoCodesUsageLimit|int
    // {
    //     $promoCodesUsageLimit = self::getByPromoCodeIdAndCustomerId($promoCode->id, $customerId);

    //     if (empty($promoCodesUsageLimit)) {
    //         $promoCodesUsageLimit = new PromoCodesUsageLimit;
    //         $promoCodesUsageLimit->promo_code_id = $promoCode->id;
    //         $promoCodesUsageLimit->customer_id = $customerId;
    //         $promoCodesUsageLimit->total_used = 1;
    //         $promoCodesUsageLimit->save();

    //         return $promoCodesUsageLimit;
    //     }

    //     return $promoCodesUsageLimit->increment('total_used');
    // }

    // public static function usageLimitReached(PromoCode $promoCodeData, int $customerId): bool
    // {
    //     $usageLimitData = self::where('promo_code_id', '=', $promoCodeData->id)
    //         ->where('customer_id', '=', $customerId)
    //         ->first();

    //     $reached = true;

    //     if (empty($usageLimitData)) {
    //         $usageLimitData = new PromoCodesUsageLimit;
    //         $usageLimitData->promo_code_id = $promoCodeData->id;
    //         $usageLimitData->customer_id = $customerId;
    //         $usageLimitData->total_used = 1;
    //         $usageLimitData->save();
    //         $reached = false;
    //     } elseif ($usageLimitData->total_used < $promoCodeData->usage_limit) {
    //         $usageLimitData->increment('total_used');
    //         $reached = false;
    //     }

    //     return $reached;
    // }

    public static function promoCodeTotalUsedByUser(int $customerId, int $promoCodeId): PromoCodesUsageLimit
    {
        return self::where('promo_code_id', '=', $promoCodeId)
            ->where('customer_id', '=', $customerId)
            ->firstOrFail();
    }
}
