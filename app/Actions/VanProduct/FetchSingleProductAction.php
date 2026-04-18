<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;

final class FetchSingleProductAction
{
    public function execute(int $productId)
{
    $van = auth()->guard('van')->user();

    return VanProduct::getById($productId, $van->id);
}
}