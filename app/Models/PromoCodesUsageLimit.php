<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodesUsageLimit extends Model
{
    use HasFactory;

    protected $table = 'promo_codes_usage_limit';

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
    public static function promoCodeUsageLimit(object $promoCodeData, int $userId): int
    {
        $usageLimitData = self::where('promo_code_id', $promoCodeData->id)
            ->where('user_id', $userId)
            ->first();

        $status = 0;

        if (empty($usageLimitData)) {
            $usageLimitData = new PromoCodesUsageLimit;
            $usageLimitData->promo_code_id = $promoCodeData->id;
            $usageLimitData->user_id = $userId;
            $usageLimitData->total_used = 1;
            $usageLimitData->save();
            $status = 1;
        } elseif ($usageLimitData->total_used < $promoCodeData->usage_limit) {
            $usageLimitData->increment('total_used');
            $status = 1;
        }

        return $status;
    }

    public static function promoCodeTotalUsedByUser(int $customerId, int $promoCodeId): ?PromoCodesUsageLimit
    {
        return self::where('promo_code_id', $promoCodeId)
            ->where('customer_id', $customerId)
            ->first();
    }
}
