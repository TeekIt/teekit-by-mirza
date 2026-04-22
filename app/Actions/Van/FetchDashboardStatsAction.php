<?php

namespace App\Actions\Van;

use App\Models\VanProduct;

final class FetchDashboardStatsAction
{
    public function execute(int $vanId): array
    {
        $products = VanProduct::where('van_id', '=', $vanId)->get();

        $totalItems = $products->count();

        $inStock = $products->where('status', 'in_stock')->count();
        $critical = $products->where('status', 'critical')->count();
        $outOfStock = $products->where('status', 'out_of_stock')->count();

        return [
            'total_items'  => $totalItems,
            'in_stock'     => $inStock,
            'critical'     => $critical,
            'out_of_stock' => $outOfStock,
        ];
    }
}
