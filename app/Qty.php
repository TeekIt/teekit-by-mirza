<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Qty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $guarded = [];
    
    protected $table = 'qty';
    /**
     * Relations
     */
    public function store()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'products_id');
    }
    /**
     * Helpers
     */
    // public static function updateProductQty(int $product_id, int $user_id, int $product_quantity)
    // {
    //     if (!empty($user_id)) {
    //         Qty::where('products_id', $product_id)
    //             ->where('users_id', $user_id)
    //             ->update(['qty' => $product_quantity]);
    //     } else if (empty($user_id)) {
    //         Qty::where('products_id', $product_id)
    //             ->decrement(['qty' => $product_quantity]);
    //     }
    //     return true;
    // }

    public static function getProductsByGivenIds(int $category_id, int $store_id)
    {
        $quantities = Qty::where('users_id', $store_id)
            ->where('category_id', $category_id)
            ->paginate(10);
        $pagination = $quantities->toArray();
        if (!$quantities->isEmpty()) {
            $products_data = [];
            foreach ($quantities as $single_index) $products_data[] = Products::getProductInfo($single_index->users_id, $single_index->products_id);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function subtractProductQty(int $user_id, int $product_id, int $product_quantity)
    {
        return Qty::where('users_id', $user_id)
            ->where('products_id', $product_id)
            ->decrement('qty', $product_quantity);
    }

    public static function getChildSellerProducts(int $user_id)
    {
        return Qty::where('qty.users_id', $user_id)
            ->join('products as prod', 'prod.id', 'qty.products_id')
            ->select('prod.*')
            ->paginate(20);
    }

    public static function updateChildProductQty(array $quantity)
    {
        return Qty::updateOrCreate(
            ['users_id' => $quantity['child_seller_id'], 'products_id' => $quantity['prod_id']],
            ['qty' => $quantity['qty']]
        );
    }
    /**
     * Since our qty has now it's separate migration,
     * this will help us add qty with given details to qty table
     * @author Muhammad Abdullah Mirza
     */
    public static function addProductQty(int $user_id, int $product_id, int $category_id, int $product_quantity)
    {
        $quantity = new Qty();
        $quantity->users_id = $user_id;
        $quantity->products_id = $product_id;
        $quantity->category_id = $category_id;
        $quantity->qty = $product_quantity;
        return $quantity->save();
    }
}
