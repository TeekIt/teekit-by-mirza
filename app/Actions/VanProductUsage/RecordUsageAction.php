<?php 

namespace App\Actions\VanProductUsage;

use App\Models\VanOperativeProductUsage; 
use App\Models\VanProduct;
use Illuminate\Support\Facades\DB;

final class RecordUsageAction{
   public function execute(array $validated)
    {
        $product = VanProduct::find($validated['productId']);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }

        $stockUpdated = $product->useQuantity($validated['quantityUsed']);

        if (!$stockUpdated) {
            return [
                'success' => false,
                'message' => 'Not enough stock available'
            ];
        }

        // Save usage record
        VanOperativeProductUsage::create([
            'van_id'         => $validated['vanId'],
            'van_product_id'     => $validated['productId'],
            'quantity_used'  => $validated['quantityUsed'],
            'job_reference'  => $validated['jobReference'],
            'used_at'        => $validated['timestamp'] ?? now(),
        ]);

        return [
            
            'remaining_stock' => $product->fresh()->quantity,
            'job_reference'   => $validated['jobReference']
        ];
    }
}