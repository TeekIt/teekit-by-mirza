<?php

namespace App\Services;

final class ProductServices
{
    /**
     * @author Muhammad Abdullah Mirza
     * @return Json string 
     */
    public static function jsonEncodeColors(array $colors): string
    {
        return json_encode(array_fill_keys($colors, true));
    }
}
