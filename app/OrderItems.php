<?php

namespace App;

use App\Enums\UserChoicesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_belongs_to_type',
        'product_belongs_to_id',
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

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'product_belongs_to_type', 'product_belongs_to_id');
    }
    /**
     * Helpers
     */
    public static function add(
        int $orderId,
        string $productBelongsToType,
        int $productBelongsToId,
        float $productPrice,
        int $qty,
        UserChoicesEnum $userChoice
    ): OrderItems {
        return self::create([
            'order_id' => $orderId,
            'product_belongs_to_type' => $productBelongsToType,
            'product_belongs_to_id' => $productBelongsToId,
            'product_price' => $productPrice,
            'product_qty' => $qty,
            'user_choice' => $userChoice
        ]);
    }

    public static function remove(int $id): int
    {
        return self::where('id', $id)->forceDelete();
    }

    public static function replaceWithAlternativeProduct(
        int $orderId,
        int $currentProdId,
        int $alternativeProdId,
        int $selectedQty
    ): int {
        return self::where('order_id', $orderId)
            ->where('product_belongs_to_type', (new Products())->getMorphClass())
            ->where('product_belongs_to_id', $currentProdId)
            ->update([
                'product_belongs_to_id' => $alternativeProdId,
                'product_qty' => $selectedQty
            ]);
    }
}
