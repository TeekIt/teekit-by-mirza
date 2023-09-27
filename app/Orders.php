<?php

namespace App;

use App\Http\Controllers\ProductsController;
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

    public static function getOrdersForView(int $order_id = null)
    {
        return Orders::with('products')
            ->when($order_id, function ($query) use ($order_id) {
                return $query->where('id', '=', $order_id);
            })
            ->orderByDesc('id')
            ->paginate(10);


        // $return_arr = [];
        // $orders = Orders::where('seller_id', '=', Auth::id())->orderByDesc('id');
        // if ($request->search) {
        //     $order = Orders::find($request->search);
        //     $order->is_viewed = 1;
        //     $order->save();
        //     $orders = $orders->where('id', '=', $request->search);
        // }
        // $orders = $orders->paginate(10);
        // $orders_p = $orders;

        // foreach ($orders as $order) {
        //     //$order_items = [];
        //     $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
        //     $item_arr = [];
        //     foreach ($items as $item) {
        //         $product = (new ProductsController())->getProductInfo($item->product_id);
        //         $item['product'] = $product;
        //         $item_arr[] = $item;
        //     }
        //     $order['items'] = $item_arr;
        //     $return_arr[] = $order;
        // }
        // $orders = $return_arr;
        // return view('shopkeeper.orders.list', compact('orders', 'orders_p'));
    }

    // public static function getParentSellerProductsDescForView(int $seller_id, string $search = '', int $category_id = null)
    // {
    //     return Products::with('category', 'rattings')
    //         ->where('product_name', 'LIKE', "%{$search}%")
    //         ->where('user_id', '=', $seller_id)
    //         ->when($category_id, function ($query, $category_id) {
    //             return $query->where('category_id', '=', $category_id);
    //         })
    //         ->orderByDesc('id')
    //         ->paginate(12);
    // }
}
