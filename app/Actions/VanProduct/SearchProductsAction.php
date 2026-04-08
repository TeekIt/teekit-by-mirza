<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class SearchProductsAction
{
    /**
     * Search products by query string across multiple columns
     *
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    public function execute(string $query)
    {
        return VanProduct::query()
            // Grouped search conditions
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('product_name', 'like', "%{$query}%")
                             ->orWhere('sku', 'like', "%{$query}%")
                             ->orWhere('brand', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->get();
    }
}