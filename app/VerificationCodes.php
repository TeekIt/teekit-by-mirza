<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCodes extends Model
{
    use HasFactory;
    /**
     * Relations
     */
    // 

    /**
     * Helpers
     */
    public static function add(int $order_id, string $verification_code): bool
    {
        $verification_codes = new VerificationCodes();
        $verification_codes->order_id = $order_id;
        $verification_codes->code = '{"code": "' . $verification_code . '", "driver_failed_to_enter_code": "NULL"}';
        return $verification_codes->save();
    }
}
