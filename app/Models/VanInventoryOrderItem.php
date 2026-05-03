<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VanInventoryOrderItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relations
     */
    public function vanInventoryOrder(): BelongsTo
    {
        return $this->belongsTo(VanInventoryOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }

    /**
     * Helpers
     */
    public static function add(
        int $vanInventoryOrderId,
        int $productId,
        int $sellerId,
        float $price,
        int $qty,
    ): self {
        return self::create([
            'van_inventory_order_id' => $vanInventoryOrderId,
            'product_id' => $productId,
            'seller_id' => $sellerId,
            'price' => $price,
            'qty' => $qty,
        ]);
    }
}
