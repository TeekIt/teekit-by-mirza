<?php

namespace App\Enums;

enum VanProductTypeEnum: string
{
    case MANUAL = 'manual';
    case PAY_AS_YOU_GO = 'payAsYouGo';
    case PRE_PAID = 'prePaid';
}
