<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    /**
     * Relations
     */
    public function orders()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function products()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
    /**
     * Helpers
     */
    public static function replaceWithAlternativeProduct(int $order_id, int $current_prod_id, int $alternative_prod_id, int $selected_qty)
    {
        return OrderItems::where('order_id', $order_id)
            ->where('product_id', $current_prod_id)
            ->update([
                'product_id' => $alternative_prod_id,
                'product_qty' => $selected_qty
            ]);
    }
}
