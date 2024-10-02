<?php

namespace App\Enums;

enum UserRole: int {
    case SUPERADMIN = 1;
    case SELLER = 2;
    case BUYER = 3;
    case CHILD_SELLER = 5;
}