<?php

namespace App\Services;

final class DateTimeServices
{
    public static function getDateOnly(string $timestamp): string
    {
        return explode(' ', $timestamp)[0];
    }

    public static function getTimeOnlyWithOutSeconds(string $timestamp): string
    {
        return substr(explode(' ', $timestamp)[1], 0, 5);
    }
}
