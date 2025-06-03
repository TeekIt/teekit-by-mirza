<?php

namespace App\Services;

final class ProductServices
{
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function jsonEncodeColors(array $colors): string
    {
        return json_encode(array_fill_keys($colors, true));
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function jsonDecodeColors(string $colors): string
    {
        return implode(', ', array_keys(json_decode($colors, true) ?? []));
    }
}
