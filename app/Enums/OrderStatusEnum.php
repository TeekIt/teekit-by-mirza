<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case READY = 'ready';
    case STUART_DELIVERY = 'stuartDelivery';
    case ON_THE_WAY = 'onTheWay';
    case DELIVERED = 'delivered';
    case COMPLETE = 'complete';
    case CANCELLED = 'cancelled';
}
