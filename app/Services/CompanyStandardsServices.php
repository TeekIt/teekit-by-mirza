<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * In this class we will declare all the standard values
 * for our project so we can use them anywhere inside the project
 * e.g standard delivery charges, standard delivery deadline, etc
 */
final class CompanyStandardsServices
{
    public static float $standardServiceCharges = 1.99;

    public static function getStandardDeliveryDeadline(): Carbon
    {
        return now()->addHour();
    }
}
