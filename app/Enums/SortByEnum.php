<?php

namespace App\Enums;

enum SortByEnum: string 
{
    case PriceLowToHigh = 'PriceLowToHigh';
    case PriceHighToLow = 'PriceHighToLow';
}