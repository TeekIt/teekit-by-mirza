<?php

namespace App\Services;

use Illuminate\Support\Str;

final class UUIDServices
{
    public static function generateUUID(): string
    {
        return Str::uuid();
    }
}
