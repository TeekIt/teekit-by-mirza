<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;
use Illuminate\Http\Request;
final class ListProductsAction
{
     public function execute(Request $request)
    {
        // Get logged-in van using auth guard
        $van = auth()->guard('van')->user();
        $vanId = $van->id;

        $filters = [
            'category_id' => $request->query('categoryID'),
            'status' => $request->query('status'),
            'id' => $request->query('productId'),
        ];

        // Call the model's getFilteredProducts method
        return VanProduct::getFilteredProducts($filters, $vanId);
    }
}