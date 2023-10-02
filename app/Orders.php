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

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'seller_id');
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

    // public function categories()
    // {
    //     return $this->hasManyThrough(
    //         Category::class,
    //         ExerciseRelation::class,
    //         // Intermediate model...
    //         'ex_id',
    //         // Foreign key on the ExerciseRelation model...
    //         'id',
    //         // Local key on the Category model...
    //         'id',
    //         // Local key on the Exercise model...
    //         'cat_id' // Foreign key on the ExerciseRelation table...
    //     );
    // }
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

    public static function isViewed(int $id)
    {
        $order = Orders::find($id);
        $order->is_viewed = 1;
        $order->save();
        return $order;
    }

    public static function getOrdersForView(int $id = null, int $seller_id)
    {
        // First we will update the "is_viewed" column if the order is searched by ID
        if ($id) static::isViewed($id);
        // Now we will fetch the required data
        $orders = Orders::with('order_items')
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '=', $id);
            })
            ->where('seller_id', '=', $seller_id)
            ->orderByDesc('id');
        
        $orders = $orders->paginate(8);
        $pagination = $orders;

        foreach ($orders as $order) {
            // Calling the products relation
            $items = $order->order_items;
            $item_arr = [];
            foreach ($items as $item) $item_arr[] = Products::getProductInfo($item->product_id);

            $order['items'] = $item_arr;
            $data[] = $order;
        }

        return ['orders' => $data, 'pagination' => $pagination];
    }
}
