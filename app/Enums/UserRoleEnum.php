<?php

namespace App\Enums;

enum UserRoleEnum: int
{
    case SUPERADMIN = 1;
    case SELLER = 2;
    case BUYER = 3;
    case COMPANY = 4;
    case CHILD_SELLER = 5;
}
