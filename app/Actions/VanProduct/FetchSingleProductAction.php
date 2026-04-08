<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class FetchSingleProductAction
{
    public function execute(int $productId, int $vanId)
    {
        return VanProduct::where('id', $productId)
                         ->where('van_id', $vanId)
                         ->first();
    }
}