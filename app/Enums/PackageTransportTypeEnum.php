<?php

namespace App\Enums;

enum PackageTransportTypeEnum: string
{
    case MOPED = 'Moped';
    case CAR_BOOT = 'Car Boot';
    case SMALL_VAN = 'Small Van';
    case BIG_VAN = 'Big Van';
}
