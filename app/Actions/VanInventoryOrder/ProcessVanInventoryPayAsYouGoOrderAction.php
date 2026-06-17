<?php

namespace App\Actions\VanInventoryOrder;

use App\Models\Products;
use App\Models\VanProduct;
use Illuminate\Support\Facades\DB;

final class ProcessVanInventoryPayAsYouGoOrderAction
{
    public function execute(int $companyId, int $vanId, array $orderItems): bool
    {
        return DB::transaction(function () use ($companyId, $vanId, $orderItems): bool {
            $productIds = array_column($orderItems, 'id');
            $products = Products::whereIn('id', $productIds)->get();
            $orderItemsMap = collect($orderItems)->keyBy('id');

            return VanProduct::addBulkFromProductsTable(
                $products,
                $orderItemsMap,
                $companyId,
                $vanId
            );
        });
    }
}
