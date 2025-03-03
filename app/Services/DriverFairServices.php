<?php

namespace App\Services;

final class DriverFairServices
{
    /**
     * The formulas used inside the function are pre-defined by Eesa & Team
     * @author Muhammad Abdullah Mirza
     */
    public static function calculateDriverFair2($total_weight, $total_volumn, $distance): float
    {
        // 38cm*38cm*38cm = 54,872cm
        if ($total_weight <= 12 || $total_volumn <= 54872) {
            // Calculate fair for Bike driver
            return round((2.6 + (1.5 * $distance)) * 0.75);
        } else {
            // Calculate fair for Car/Van driver
            return round(((2.6 + (1.75 * $distance)) + ((($total_weight - 12) / 15) * ($distance / 4))) * 0.8);
        }
    }
}
