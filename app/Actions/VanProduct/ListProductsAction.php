<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;
use Illuminate\Http\Request;
final class ListProductsAction
{
     public function execute(object $filters)
{
    $vanId = auth()->guard('van')->id();

    return VanProduct::getFilteredProducts([
        'category_id' => $filters->categoryID ?? null,
        'status' => $filters->status ?? null,
        'id' => $filters->productId ?? null,
    ], $vanId);
}
}