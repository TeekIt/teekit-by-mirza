<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;

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
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'products_id');
    }
    /**
     * Helpers
     */
    // public static function updateProductQty(int $product_id, int $user_id, int $product_quantity)
    // {
    //     if (!empty($user_id)) {
    //         self::where('products_id', $product_id)
    //             ->where('users_id', $user_id)
    //             ->update(['qty' => $product_quantity]);
    //     } else if (empty($user_id)) {
    //         self::where('products_id', $product_id)
    //             ->decrement(['qty' => $product_quantity]);
    //     }
    //     return true;
    // }

    public static function getTotalProductsCountBySellerId(int $seller_id): int
    {
        return self::where('users_id', '=', $seller_id)->count();
    }

    public static function getSellersByGivenParams(int $category_id, string $state): object
    {
        return self::select([
            'users.id',
            'users.name',
            'users.email',
            'users.business_name',
            'users.business_hours',
            'users.full_address',
            'users.unit_address',
            'users.country',
            'users.state',
            'users.city',
            'users.lat',
            'users.lon',
            'users.user_img',
            'users.pending_withdraw',
            'users.total_withdraw',
            'users.parent_store_id',
            'users.is_online',
            'users.role_id'
        ])
            ->join('users', 'users.id', '=', 'qty.users_id')
            ->join('products', 'products.id', '=', 'qty.products_id')
            ->where('qty.qty', '>', 0) // Products should be in stock
            ->where('qty.category_id', '=', $category_id)
            ->where('products.status', '=', 1) // Products should be live
            ->where('users.is_active', '=', 1) // Sellers should be active
            ->where('users.state', '=', $state)
            ->distinct() // Use distinct to select only unique stores
            ->get();
    }

    public static function getProductsByGivenIds(int $category_id, int $seller_id): array
    {
        $quantities = self::where('users_id', $seller_id)
            ->where('category_id', $category_id)
            ->paginate(10);
        $pagination = $quantities->toArray();
        if (!$quantities->isEmpty()) {
            $products_data = [];
            foreach ($quantities as $single_index)
                $products_data[] = Products::getProductInfo($single_index->users_id, $single_index->products_id, [
                    'id',
                    'user_id',
                    'category_id',
                    'product_name',
                    'sku',
                    'price',
                    'featured',
                    'discount_percentage',
                    'weight',
                    'brand',
                    'size',
                    'bike',
                    'car',
                    'van',
                    'feature_img',
                    'height',
                    'width',
                    'length',
                ]);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function getChildSellerProducts(int $user_id): LengthAwarePaginator
    {
        return self::where('qty.users_id', $user_id)
            ->join('products as prod', 'prod.id', 'qty.products_id')
            ->select('prod.*')
            ->paginate(20);
    }

    public static function subtractProductQty(int $user_id, int $product_id, int $product_quantity): int
    {
        return self::where('users_id', $user_id)
            ->where('products_id', $product_id)
            ->decrement('qty', $product_quantity);
    }

    public static function updateChildProductQty(array $quantity): Qty
    {
        return self::updateOrCreate(
            ['users_id' => $quantity['child_seller_id'], 'products_id' => $quantity['prod_id']],
            ['qty' => $quantity['qty']]
        );
    }
    /**
     * Since our qty has now it's separate migration,
     * this will help us add qty with given details to qty table
     * @author Muhammad Abdullah Mirza
     */
    public static function add(int $user_id, int $product_id, int $category_id, int $product_quantity): bool
    {
        $quantity = new Qty();
        $quantity->users_id = $user_id;
        $quantity->products_id = $product_id;
        $quantity->category_id = $category_id;
        $quantity->qty = $product_quantity;
        return $quantity->save();
    }
}
