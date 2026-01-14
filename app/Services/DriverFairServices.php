<?php

namespace App\Services;

final class DriverFairServices
{
    /**
     * The formulas used inside the function are pre-defined by Eesa & Team
     *
     * @author Muhammad Abdullah Mirza
     */
    public static function calculateDriverFair2($totalWeight, $totalVolumn, $distance): float
    {
        /* 38cm*38cm*38cm = 54,872cm */
        if ($totalWeight <= 12 || $totalVolumn <= 54872) {
            /* Calculate fair for Bike driver */
            return round((2.6 + (1.5 * $distance)) * 0.75);
        } else {
            /* Calculate fair for Car/Van driver */
            return round(((2.6 + (1.75 * $distance)) + ((($totalWeight - 12) / 15) * ($distance / 4))) * 0.8);
        }
    }
}
