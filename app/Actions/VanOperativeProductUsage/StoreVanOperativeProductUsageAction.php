<?php

namespace App\Actions\VanOperativeProductUsage;

use App\Models\VanOperativeProductUsage;
use App\Models\VanProduct;

final class StoreVanOperativeProductUsageAction
{
    public function execute(array $filters): string|VanOperativeProductUsage
    {
        $vanProduct = VanProduct::find($filters['productId']);
        
        $stockUpdated = $vanProduct->useQuantity($filters['quantityUsed']);

        if (!$stockUpdated) {
            return 'Not enough stock available';
        }

        return VanOperativeProductUsage::add($filters);
    }
}
