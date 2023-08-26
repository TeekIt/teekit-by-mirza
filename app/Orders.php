<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    /**
     * Helpers
     */
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
        return Orders::where('user_id', $user_id)->count();
    }

    public static function updateOrderStatus(int $id, string $status)
    {
        return Orders::where('id', $id)->update([
            'order_status' => $status
        ]);
    }
}
