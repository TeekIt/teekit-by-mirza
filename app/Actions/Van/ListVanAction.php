<?php

namespace App\Actions\Van;

use App\Models\Van;

final class ListVanAction
{
    public function execute(int $vanId, array $columns = ['*']): Van
    {
        return Van::getById($vanId, $columns);
    }
}
