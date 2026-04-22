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
        VanOperativeProductUsage::add($validated);

        return [
            'success' => true,
            'remaining_stock' => $product->fresh()->quantity,
            'job_reference'   => $validated['jobReference']
        ];
    }
        
}