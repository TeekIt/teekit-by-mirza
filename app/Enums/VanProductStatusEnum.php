<?php

namespace App\Enums;

enum VanProductStatusEnum: string
{
    case IN_STOCK = 'inStock';
    case LOW_STOCK = 'lowStock';
    case OUT_OF_STOCK = 'outOfStock';
}
