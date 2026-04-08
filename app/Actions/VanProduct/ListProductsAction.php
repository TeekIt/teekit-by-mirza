<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class ListProductsAction
{
    public function execute(array $filters, int $vanId)
    {
        return VanProduct::query()
            ->where('van_id', $vanId)
            ->when(!empty($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                // Map API status to DB quantity conditions
                if ($filters['status'] === 'in_stock') {
                    $query->whereColumn('quantity', '>', 'min_threshold');
                } elseif ($filters['status'] === 'critical') {
                    $query->whereColumn('quantity', '<=', 'min_threshold')
                          ->where('quantity', '>', 0);
                } elseif ($filters['status'] === 'out_of_stock') {
                    $query->where('quantity', 0);
                }
            })
            ->get();
    }
}