<?php

namespace App\Enums;

enum DeliveryStatusEnum: string
{
    case ASSIGNED = 'assigned';
    case PENDING_APPROVAL = 'pending_approval';
    case COMPLETE = 'complete';
    case CANCELLED = 'cancelled';
}
