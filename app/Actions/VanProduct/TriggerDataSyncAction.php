<?php

namespace App\Actions\VanProducts;

class TriggerDataSyncAction
{
    public function execute()
    {
        return [
            'message'    => 'Data synchronization triggered successfully',
            'synced_at'  => now()->toDateTimeString(),
            'status'     => 'success'
        ];
    }
}