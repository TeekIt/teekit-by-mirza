<?php

namespace App\Enums;

enum VanInventoryProductStatus: string
{
    case IN_STOCK = 'inStock';
    case LOW_STOCK = 'lowStock';
    case OUT_OF_STOCK = 'outOfStock';
}
