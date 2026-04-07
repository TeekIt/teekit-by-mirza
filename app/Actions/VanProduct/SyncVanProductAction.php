<?php

namespace App\Actions\VanProduct;

use App\Models\Van;

final class SyncVanProductAction
{
    // Fetch all van products by van ID
    public function execute(int $vanId)
    {
        return VanProduct::getByVanId($vanId);
    }

    // Fetch van details by van ID
    public function executeById(int $vanId)
    {
        $van = Van::getDetailsById($vanId);

        if (!$van) {
            return []; // return empty if van not found
        }

        return [
            'operative_name' => $van->operative?? null,
            'van_id'         => $van->id,
            'number_plate'   => $van->number_plate,
            'username'       => $van->user_name,
        ];
    }
}