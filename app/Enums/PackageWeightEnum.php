<?php

namespace App\Enums;

enum PackageWeightEnum: string
{
    case SMALL = 'Up to 8kg';
    case MEDIUM = 'Up to 12kg';
    case LARGE = 'Up to 40kg';
    case EXTRA_LARGE = 'Up to 70kg';
}
