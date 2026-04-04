<?php

namespace App\Actions\VanInventory;

use App\Models\VanInventory;

/**
 * Van Inventory record create Action
 */
class CreateVanInventoryAction
{
    /**
     * Execute the action
     *
     * @param object $data
     * @return VanInventory
     */
       public function execute(array $data) // array
    {
        return VanInventory::create([
            'product_name' => $data['product_name'],
            'qty'          => $data['qty'] ?? 0,
            'price'        => $data['price'],
        ]);
    }
}