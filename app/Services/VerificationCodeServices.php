<?php

namespace App\Services;

final class VerificationCodeServices
{
    public static function generateCode()
    {
        $verification_code = '';
        while (strlen($verification_code) < 6) {
            $rand_number = rand(0, time());
            $verification_code = $verification_code . substr($rand_number, 0, 1);
        }
        return $verification_code;
    }
}
