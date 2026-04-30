<?php

namespace App\Actions\VanProduct;

use App\Enums\OrderByEnum;
use App\Enums\VanProductStatusEnum;
use App\Models\VanProduct;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListVanProductsAction
{
    public function execute(array $filters): VanProduct|LengthAwarePaginator
    {
        if(isset($filters['productId'])){
            return VanProduct::getById($filters['productId']);
        }

        return VanProduct::getAll(
            orderBy:OrderByEnum::DESC,
            /* Get logged-in van using "van" auth guard */
            vanId: auth()->guard('van')->user()->id,
            categoryId: $filters['categoryId'] ?? null,
            status: isset($filters['status']) ? VanProductStatusEnum::tryFrom($filters['status']) : null,
        );
    }
}
