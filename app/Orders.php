<?php

namespace App;

use App\Http\Controllers\ProductsController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Orders extends Model
{
    protected $fillable = ['*'];
    /**
     * Relations
     */
    public function order_items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function products()
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
    public static function subFromOrderTotal(int $order_id, float $prod_total_price)
    {
        $order = self::find($order_id);
        $order->order_total = $order->order_total - $prod_total_price;
        return $order->save();
    }

    public static function replaceWithAlternativePrice(int $order_id, float $current_prod_price, float $alternative_prod_price)
    {
        $order = self::find($order_id);
        $order->order_total = ($order->order_total - $current_prod_price) + $alternative_prod_price;
        return $order->save();
    }

    public static function fetchTransportType(int $order_id = null)
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

    public static function checkTotalOrders(int $user_id)
    {
        return self::where('user_id', $user_id)->count();
    }

    public static function updateOrderStatus(int $id, string $status)
    {
        return self::where('id', $id)->update([
            'order_status' => $status
        ]);
    }

    public static function isViewed(int $id)
    {
        $order = self::find($id);
        $order->is_viewed = 1;
        $order->save();
        return $order;
    }

    public static function getOrdersForView(int|null $id = null, int $seller_id, string $order_by)
    {
        // First we will update the "is_viewed" column if the order is searched by ID
        if ($id) static::isViewed($id);
        // Now we will fetch the required data
        return self::with(['order_items', 'products.category'])
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '=', $id);
            })
            ->where('seller_id', '=', $seller_id)
            ->orderBy('created_at', $order_by)
            ->paginate(10);
    }

    public static function getRecentOrderByBuyerId(int $buyer_id, int|null $prducts_limit = null, int|null $seller_id = null): object
    {
        return self::with([
            'products' => function ($query) use ($prducts_limit) {
                if($prducts_limit !== null) $query->take($prducts_limit);
            }
        ])
            ->when($seller_id, fn ($query) => $query->where('seller_id', $seller_id))
            ->where('user_id', $buyer_id)
            ->latest()
            ->first();
    }

    public static function getOrderById(int $id)
    {
        return self::with(['order_items', 'user', 'store', 'delivery_boy'])->where('id', $id)->first();
    }
}
