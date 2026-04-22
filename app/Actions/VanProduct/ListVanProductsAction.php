<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class ListVanProductsAction
{
    public function execute(array $filters)
    {
        /* Get logged-in van using "van" auth guard */
        $vanId = auth()->guard('van')->user()->id;

        if(isset($filters['productId'])){
            return VanProduct::getById($filters['productId']);
        }

        return VanProduct::getFilteredProducts(
            [
                'category_id' => $filters['categoryId'] ?? null,
                'status' => $filters['status'] ?? null,
                'id' => $filters['productId'] ?? null,
            ],
            $vanId
        );
    }
}
