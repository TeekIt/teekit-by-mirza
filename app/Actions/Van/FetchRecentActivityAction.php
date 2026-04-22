<?php

namespace App\Actions\Van;

use App\Models\VanProduct;
use Illuminate\Support\Collection;

final class FetchRecentActivityAction
{
    public function execute(int $vanId): Collection
    {
        return VanProduct::where('van_id', $vanId)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['product_name', 'job_reference', 'quantity', 'updated_at']);
    }
}