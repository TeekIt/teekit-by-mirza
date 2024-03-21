<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_price',
        'product_qty',
        'user_choice'
    ];
    /**
     * Relations
     */
    public function orders(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
    /**
     * Helpers
     */
    public static function insertOrderItem(int $order_id, int $product_id, float $product_price, int $qty, int $user_choice): OrderItems
    {
        return self::create([
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_price' => $product_price,
            'product_qty' => $qty,
            'user_choice' => $user_choice
        ]);
    }

    public static function removeItem(int $id): int
    {
        return self::where('id', $id)->delete();
    }

    public static function replaceWithAlternativeProduct(int $order_id, int $current_prod_id, int $alternative_prod_id, int $selected_qty): int
    {
        return self::where('order_id', $order_id)
            ->where('product_id', $current_prod_id)
            ->update([
                'product_id' => $alternative_prod_id,
                'product_qty' => $selected_qty
            ]);
    }
}
