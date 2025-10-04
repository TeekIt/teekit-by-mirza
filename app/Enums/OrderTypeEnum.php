<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    // case DELIVERY = 'delivery';
    case SAME_DAY_DELIVERY = 'sameDayDelivery';
    case FAST_DELIVERY = 'fastDelivery';
    case SCHEDULED = 'scheduled';
    case SELF_PICKUP = 'self-pickup';
}
