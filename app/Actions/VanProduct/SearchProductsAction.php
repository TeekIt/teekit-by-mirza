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
        return VanProduct::with(['seller', 'category'])
            ->when($query, function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhereHas('category', fn($q2) => $q2->where('category_name', 'like', "%{$query}%"))
                  ->orWhereHas('seller', fn($q3) => $q3->where('name', 'like', "%{$query}%")
                                                     ->orWhere('email', 'like', "%{$query}%")
                                                     ->orWhere('country', 'like', "%{$query}%"));
            })
            ->latest()
            ->get();
    }
}