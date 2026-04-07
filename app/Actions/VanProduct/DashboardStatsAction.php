<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class DashboardStatsAction
{
    /**
     * Fetch dashboard statistics for a specific van
     *
     * @param int $vanId
     * @return array
     */
    public function execute(int $vanId): array
    {
        $products = VanProduct::where('van_id', $vanId)->get();

        $totalItems = $products->count();

        $inStock = $products->filter(function ($product) {
            return $product->quantity > $product->min_threshold;
        })->count();

        $critical = $products->filter(function ($product) {
            return $product->quantity <= $product->min_threshold && $product->quantity > 0;
        })->count();

        return [
            'total_items' => $totalItems,
            'in_stock'    => $inStock,
            'critical'    => $critical,
        ];
    }
}