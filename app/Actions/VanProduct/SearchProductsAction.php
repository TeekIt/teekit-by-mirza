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
        $vanId = auth()->guard('van')->id();

        return VanProduct::searchByVan($vanId, $query);
    }
}