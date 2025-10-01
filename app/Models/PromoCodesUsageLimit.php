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

    /**
     * function will return boolean values i.e 1 = true, 0 = false
     */
    public static function usageLimitReached(PromoCode $promoCodeData, int $customerId): bool
    {
        $usageLimitData = self::where('promo_code_id', '=', $promoCodeData->id)
            ->where('customer_id', '=', $customerId)
            ->first();
        
        $reached = true;

        if (empty($usageLimitData)) {
            $usageLimitData = new PromoCodesUsageLimit;
            $usageLimitData->promo_code_id = $promoCodeData->id;
            $usageLimitData->customer_id = $customerId;
            $usageLimitData->total_used = 1;
            $usageLimitData->save();
            $reached = false;
        } elseif ($usageLimitData->total_used < $promoCodeData->usage_limit) {
            $usageLimitData->increment('total_used');
            $reached = false;
        }

        return $reached;
    }

    public static function promoCodeTotalUsedByUser(int $customerId, int $promoCodeId): PromoCodesUsageLimit
    {
        return self::where('promo_code_id', '=', $promoCodeId)
            ->where('customer_id', '=', $customerId)
            ->findOrFail();
    }
}
