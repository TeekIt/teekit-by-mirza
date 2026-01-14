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
    public const float STANDARD_SERVICE_CHARGES = 1.99;

    public const int STANDARD_NEAR_BY_MILES = 5;

    public static function getStandardPickUpTime(): Carbon
    {
        return now()->addMinutes(15);
    }

    public static function getStandardDeliveryDeadline(): Carbon
    {
        return now()->addHour();
    }
}
