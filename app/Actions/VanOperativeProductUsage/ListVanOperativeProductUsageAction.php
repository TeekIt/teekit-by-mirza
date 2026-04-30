<?php

namespace App\Actions\VanOperativeProductUsage;

use App\Enums\OrderByEnum;
use App\Models\VanOperativeProductUsage;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListVanOperativeProductUsageAction
{
    public function execute(): LengthAwarePaginator
    {
        return VanOperativeProductUsage::getAll(
            orderBy:OrderByEnum::DESC,
            vanId: auth()->guard('van')->id(),
            columns: ['id', 'van_product_id', 'quantity_used', 'used_at']
        );
    }
}
