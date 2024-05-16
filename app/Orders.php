<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

class Orders extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['*'];
    /**
     * Relations
     */
    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Products::class,
            OrderItems::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }
    /**
     * Helpers
     */
    public static function subFromOrderTotal(int $order_id, float $prod_total_price): bool
    {
        $order = self::find($order_id);
        $order->order_total = $order->order_total - $prod_total_price;
        return $order->save();
    }

    public static function replaceWithAlternativePrice(int $order_id, float $current_prod_price, float $alternative_prod_price): bool
    {
        $order = self::find($order_id);
        $order->order_total = ($order->order_total - $current_prod_price) + $alternative_prod_price;
        return $order->save();
    }

    public static function fetchTransportType(int $order_id = null): string
    {
        $transposrt_type = [];
        $product_ids = OrderItems::where('order_id', '=', $order_id)->pluck('product_id');
        $products = Products::whereIn('id', $product_ids)->get();
        /**
         * First populate the array $transposrt_type
         */
        foreach ($products as $single_product) {
            if ($single_product->van)
                array_push($transposrt_type, "van");
            elseif ($single_product->car)
                array_push($transposrt_type, "car");
            elseif ($single_product->bike)
                array_push($transposrt_type, "bike");
        }
        /**
         * Now if any product contains "van" then the function should return "van"
         * If any product contains "car" then return "car"
         * Otherwise "bike"
         */
        if (in_array("van", $transposrt_type))
            return "van";
        elseif (in_array("car", $transposrt_type))
            return "car";
        elseif (in_array("bike", $transposrt_type))
            return "bike";
    }

    public static function checkIfOrderExists(int $order_id): bool
    {
        return self::where('id', $order_id)->exists();
    }

    public static function checkTotalOrders(int $customer_id): int
    {
        return self::where('customer_id', $customer_id)->count();
    }

    public static function updateOrderStatus(int $order_id, string $status): int
    {
        return self::where('id', $order_id)->update([
            'order_status' => $status
        ]);
    }

    public static function isViewed(int $order_id): object
    {
        $order = self::findOrFail($order_id);
        $order->is_viewed = 1;
        $order->save();
        return $order;
    }

    public static function getTotalSalesBySellerId(int $seller_id): float
    {
        return self::where('payment_status', '=', 'paid')->where('seller_id', '=', $seller_id)->sum('order_total');
    }

    public static function getTotalOrdersBySellerId(int $seller_id): Collection
    {
        return self::where('payment_status', '!=', 'hidden')->where('seller_id', '=', $seller_id)->get();
    }

    public static function getOrdersByStatusWhereSellerId(int $seller_id, string $status): Collection
    {
        return self::where('order_status', '=', $status)->where('seller_id', '=', $seller_id)->get();
    }

    public static function getOrdersForView(int|null $order_id = null, int $seller_id, string $order_by): LengthAwarePaginator
    {
        /* First we will update the "is_viewed" column if the order is searched by ID */
        if ($order_id) static::isViewed($order_id);
        /* Now we will fetch the required data */
        return self::with(['order_items', 'products.category'])
            ->when($order_id, function ($query) use ($order_id) {
                return $query->where('id', '=', $order_id);
            })
            ->where('seller_id', '=', $seller_id)
            ->orderBy('created_at', $order_by)
            ->paginate(10);
    }

    public static function getRecentOrderByBuyerId(int $customer_id, int|null $prducts_limit = null, int|null $seller_id = null): ?Orders
    {
        return self::with([
            'products' => function ($query) use ($prducts_limit) {
                if ($prducts_limit !== null) $query->take($prducts_limit);
            }
        ])
            ->when($seller_id, fn ($query) => $query->where('seller_id', $seller_id))
            ->where('customer_id', $customer_id)
            ->latest()
            ->first();
    }

    public static function getOrderById(int $order_id): ?Orders
    {
        return self::with(['order_items', 'customer', 'store'])->where('id', $order_id)->first();
    }
}
