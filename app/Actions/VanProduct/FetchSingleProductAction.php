<?php

namespace App\Actions\VanProduct;

use App\Models\VanProduct;
final class FetchSingleProductAction
{
    /**
     * Fetch details of a single product by product ID
     *
     * @param int $productId
     * @return array
     */
    public function execute(int $productId): array
    {
        $product = VanProduct::getByProductId($productId); // Model method handles DB query

        if (!$product) {
            return []; // Return empty if product not found
        }

        return [
            'id'              => $product->id,
            'product_name'    => $product->product_name,
            'sku'             => $product->sku,
            'price'           => $product->price,
            'quantity'        => $product->quantity,
            'status'          => $product->status,
            'current_stock'   => $product->quantity,
            'min_threshold'   => $product->min_threshold ?? null,
            'unit_of_measure' => $product->unit ?? null,
            'category'        => $product->category->name ?? null,
            'seller'          => [
                'id'    => $product->seller->id ?? null,
                'name'  => $product->seller->name ?? null,
                'email' => $product->seller->email ?? null,
            ],
        ];
    }
}