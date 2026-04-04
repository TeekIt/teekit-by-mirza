<?php

namespace App\Actions\VanInventory;

use App\Models\VanInventory;

class ListVanInventoryAction{
    /**
     * Execute the action
     *
     * @param array $data
     * @return mixed
     */
    public function execute(array $data = []){
        $query = VanInventory::query();
        if(!empty($data['id'])){
            return $query->findOrFail($data['id']);
        }
        return $query->latest()->get();
    }
}