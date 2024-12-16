<?php

namespace App\Enums;

enum UserMorphTypeEnum: string
{
    case USER = 'User';
    case GUEST_BUYER = 'GuestBuyer';
}
