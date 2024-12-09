<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    case DELIVERY = 'delivery';
    case SELF_PICKUP = 'self-pickup';
}
