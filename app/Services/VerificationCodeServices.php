<?php

namespace App\Services;

final class VerificationCodeServices
{
    public static function generateCode(): string
    {
        $verificationCode = '';

        while (strlen($verificationCode) < 6) {
            $randNumber = rand(0, time());
            $verificationCode .= substr($randNumber, 0, 1);
        }

        return $verificationCode;
    }
}
