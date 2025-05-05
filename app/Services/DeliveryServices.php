<?php

namespace App\Services;

use Carbon\Carbon;

final class DeliveryServices
{
    public static function getStandardDeliveryDeadline(): Carbon
    {
        return now()->addMinutes(90);
    }
}
