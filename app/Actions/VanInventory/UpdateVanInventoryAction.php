<?php

namespace App\Actions\VanInventory;

use App\Models\VanInventory;

/**
 * Van Inventory record update Action
 */
class UpdateVanInventoryAction
{
    /**
     * Execute the action
     *
     * @param int $id
     * @param object $data
     * @return VanInventory
     */
    public function execute(int $id, object $data)
    {
        $inventory = VanInventory::findOrFail($id);

        $inventory->update([
            'product_name' => $data->product_name ?? $inventory->product_name,
            'qty'          => $data->qty ?? $inventory->qty,
            'price'        => $data->price ?? $inventory->price,
        ]);

        return $inventory->fresh();
    }
}