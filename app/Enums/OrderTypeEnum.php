<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    case SAME_DAY_DELIVERY = 'sameDayDelivery';
    case SUPER_FAST_DELIVERY = 'superFastDelivery';
    case SCHEDULED = 'scheduled';
    case SELF_PICKUP = 'self-pickup';
    case VAN_INVENTORY = 'van-inventory';
}
