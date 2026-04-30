<?php

namespace App\Actions\VanProduct;

use App\Enums\OrderByEnum;
use App\Models\VanProduct;
use Illuminate\Pagination\LengthAwarePaginator;

final class SearchProductsAction
{
    public function execute(string $query): LengthAwarePaginator
    {
        return VanProduct::getAll(
            orderBy: OrderByEnum::DESC,
            vanId: auth()->guard('van')->id(),
            search: $query
        );
    }
}
