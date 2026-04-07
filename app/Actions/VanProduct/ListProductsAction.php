<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class ListProductsAction
{
    public function execute(array $filters = [])
    {
        $query = VanProduct::with(['seller', 'category']);

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);

            if ($status === 'critical') {
                $query->whereColumn('quantity', '<=', 'min_threshold')
                      ->where('quantity', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', 0);
            } else {
                $query->where('status', $status);
            }
        }

        $products = $query->latest()->get();

        // Add dynamic status field based on quantity & threshold
        $products->transform(function ($product) {
            if ($product->quantity == 0) {
                $product->status = 'out_of_stock';
            } elseif ($product->quantity <= $product->min_threshold) {
                $product->status = 'critical';
            }
            // Else status remains as in DB (active/inactive)
            return $product;
        });

        return $products;
    }
}