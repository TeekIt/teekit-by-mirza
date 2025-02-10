<?php

namespace App\Services;

use Carbon\Carbon;

final class DeliveryServices {
    public static function getStandardDeliveryDeadline(): Carbon
    {
        return now()->addMinute(30);
    }
}