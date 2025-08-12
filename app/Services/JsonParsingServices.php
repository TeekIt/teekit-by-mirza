<?php

namespace App\Services;

use stdClass;

final readonly class JsonParsingServices
{
    public static function convertStdClassToArray(stdClass $stdClass): array
    {
        return json_decode(json_encode($stdClass), true);
    }
}
