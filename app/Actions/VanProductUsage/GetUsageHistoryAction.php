<?php

namespace App\Actions\VanProductUsage;

use App\Models\VanOperativeProductUsage;

final class GetUsageHistoryAction
{
      public function execute()
    {
        $vanId = auth()->guard('van')->id();

        return VanOperativeProductUsage::getHistoryByVan($vanId)
            ->map(function ($record) {
                return [
                    'operative_name' => $record->van->operative ?? 'Unknown',
                    'quantity_used'  => $record->quantity_used,
                    'job_reference'  => $record->job_reference,
                    'used_at'        => $record->used_at->format('d-m-Y H:i'),
                ];
            });
    }
}