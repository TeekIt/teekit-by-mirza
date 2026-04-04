<?php

namespace App\Actions\VanInventory;

use App\Models\VanInventory;

/**
 * Van Inventory record delete Action
 */
class DeleteVanInventoryAction
{
    /**
     * Execute the action
     *
     * @param int $id
     * @return array
     */
    public function execute(int $id)
    {
        $inventory = VanInventory::findOrFail($id);
        $inventory->delete();

        return ['message' => 'Van Inventory deleted successfully'];
    }
}