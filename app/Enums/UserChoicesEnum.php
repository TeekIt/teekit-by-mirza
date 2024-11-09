<?php

namespace App\Enums;

enum UserChoicesEnum: int
{
    case ALTERNATIVE_PRODUCT = 1;
    case REMOVE_PRODUCT = 2;
    case SEND_TO_OTHER_STORES = 3;
    case CALL_ME = 4;
    case CANCEL_ORDER = 5;
}
