<?php

namespace App;

use App\Enums\UserChoicesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItems extends Model
{
    use HasFactory, SoftDeletes;

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
    public static function add(
        int $orderId,
        int $productId,
        float $productPrice,
        int $qty,
        UserChoicesEnum $userChoice
    ): OrderItems {
        return self::create([
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_price' => $productPrice,
            'product_qty' => $qty,
            'user_choice' => $userChoice
        ]);
    }

    public static function removeItem(int $id): int
    {
        return self::where('id', $id)->delete();
    }

    public static function replaceWithAlternativeProduct(
        int $orderId,
        int $currentProdId,
        int $alternativeProdId,
        int $selectedQty
    ): int {
        return self::where('order_id', $orderId)
            ->where('product_id', $currentProdId)
            ->update([
                'product_id' => $alternativeProdId,
                'product_qty' => $selectedQty
            ]);
    }
}
