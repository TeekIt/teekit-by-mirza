<?php

namespace App\Actions\Van;

use App\Models\Van;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListVanAction
{
    public function execute(array $filters, array $columns = ['*']): Van|LengthAwarePaginator
    {
        if (isset($filters['id'])) {
            return Van::getById($filters['id'], $columns);
        }

        return Van::getAll(
            $filters['orderBy'], 
            $filters['search'], 
            $filters['companyId'] ?? null,
            $columns
        );
    }
}
