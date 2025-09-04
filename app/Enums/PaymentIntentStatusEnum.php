<?php

namespace App\Enums;

enum PaymentIntentStatusEnum: string
{
    case SUCCEEDED = 'succeeded';
    case CANCELED = 'canceled';
}
