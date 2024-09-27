<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Qty extends Model
{
    use HasFactory, SoftDeletes;
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
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function productImage(): HasMany
    {
        return $this->hasMany(productImages::class, 'product_id', 'product_id');
    }
    /**
     * Helpers
     */
    // public static function updateProductQty(int $product_id, int $seller_id, int $product_quantity)
    // {
    //     if (!empty($seller_id)) {
    //         self::where('product_id', $product_id)
    //             ->where('seller_id', $seller_id)
    //             ->update(['qty' => $product_quantity]);
    //     } else if (empty($seller_id)) {
    //         self::where('product_id', $product_id)
    //             ->decrement(['qty' => $product_quantity]);
    //     }
    //     return true;
    // }

    public static function getTotalProductsCountBySellerId(int $seller_id): int
    {
        return self::where('seller_id', '=', $seller_id)->count();
    }

    public static function syncParentSellerQuantities(int $parent_seller_id, int $child_seller_id): bool
    {
        return DB::statement("
                INSERT INTO `qty` (`seller_id`, `product_id`, `category_id`, `qty`)
                SELECT $child_seller_id, `product_id`, `category_id`, `qty`
                FROM `qty`
                WHERE `seller_id` = $parent_seller_id
            ");
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
            ->join('users', 'users.id', '=', 'qty.seller_id')
            ->join('products', 'products.id', '=', 'qty.product_id')
            ->where('qty.qty', '>', 0) // Products should be in stock
            ->where('qty.category_id', '=', $category_id)
            ->where('products.status', '=', '1') // Products should be live
            ->where('users.is_active', '=', 1) // Sellers should be active
            ->where('users.state', '=', $state)
            ->distinct() // Use distinct to select only unique stores
            ->get();
    }

    public static function getProductsByGivenIds(int $category_id, int $seller_id): array
    {
        $paginated_data = self::select('id', 'seller_id', 'product_id', 'category_id', 'qty')
            ->with([
                'product:id,product_name,sku,price,featured,discount_percentage,weight,brand,size,bike,car,van,feature_img,height,width,length',
                'store:id,business_name,business_hours,full_address,country,state,city,lat,lon,user_img',
                'category:id,category_name,category_image',
                'productImage:id,product_id,product_image',
            ])
            ->where('category_id', $category_id)
            ->where('seller_id', $seller_id)
            ->paginate(10);

        if (!$paginated_data->isEmpty()) {
            $products_data = $paginated_data->map(function ($singleIndex) {
                return [
                    'id' => $singleIndex->product_id,
                    'seller_id' => $singleIndex->seller_id,
                    'category_id' => $singleIndex->category_id,
                    'product_name' => $singleIndex->product->product_name,
                    'sku' => $singleIndex->product->sku,
                    'price' => $singleIndex->product->price,
                    'featured' => $singleIndex->product->featured,
                    'discount_percentage' => $singleIndex->product->discount_percentage,
                    'weight' => $singleIndex->product->weight,
                    'brand' => $singleIndex->product->brand,
                    'size' => $singleIndex->product->size,
                    'bike' => $singleIndex->product->bike,
                    'car' => $singleIndex->product->car,
                    'van' => $singleIndex->product->van,
                    'feature_img' => $singleIndex->product->feature_img,
                    'height' => $singleIndex->product->height,
                    'width' => $singleIndex->product->width,
                    'length' => $singleIndex->product->length,
                    'store' => [
                        'id' => $singleIndex->store->id,
                        'business_name' => $singleIndex->store->business_name,
                        'business_hours' => $singleIndex->store->business_hours,
                        'full_address' => $singleIndex->store->full_address,
                        'country' => $singleIndex->store->country,
                        'state' => $singleIndex->store->state,
                        'city' => $singleIndex->store->city,
                        'lat' => $singleIndex->store->lat,
                        'lon' => $singleIndex->store->lon,
                        'user_img' => $singleIndex->store->user_img,
                    ],
                    'qty' => [
                        'id' => $singleIndex->id,
                        'product_id' => $singleIndex->product_id,
                        'qty' => $singleIndex->qty,
                    ],
                    'images' => $singleIndex->productImage->map(function ($singleImage) {
                        return [
                            'id' => $singleImage->id,
                            'product_image' => $singleImage->product_image,
                        ];
                    })->toArray(),
                    'category' => [
                        'id' => $singleIndex->category->id,
                        'category_name' => $singleIndex->category->category_name,
                        'category_image' => $singleIndex->category->category_image,
                    ]
                ];
            });

            $paginated_data = $paginated_data->toArray();
            unset($paginated_data['data']);

            return ['data' => $products_data, 'pagination' => $paginated_data];
        } else {
            return [];
        }
    }

    public static function getChildSellerProducts(int $seller_id): LengthAwarePaginator
    {
        return self::where('qty.seller_id', $seller_id)
            ->join('products as prod', 'prod.id', 'qty.product_id')
            ->select('prod.*')
            ->paginate(20);
    }

    public static function subtractProductQty(int $seller_id, int $product_id, int $product_quantity): int
    {
        return self::where('seller_id', $seller_id)
            ->where('product_id', $product_id)
            ->decrement('qty', $product_quantity);
    }

    public static function updateChildProductQty(array $quantity): Qty
    {
        return self::updateOrCreate(
            [
                'seller_id' => $quantity['child_seller_id'],
                'product_id' => $quantity['prod_id'],
                'category_id' => $quantity['category_id'],
            ],
            ['qty' => $quantity['qty']]
        );
    }
    /**
     * Since our qty has now it's separate migration,
     * this will help us add qty with given details to qty table
     * @author Muhammad Abdullah Mirza
     */
    public static function add(int $seller_id, int $product_id, int $category_id, int $product_quantity): bool
    {
        $quantity = new Qty();
        $quantity->seller_id = $seller_id;
        $quantity->product_id = $product_id;
        $quantity->category_id = $category_id;
        $quantity->qty = $product_quantity;
        return $quantity->save();
    }
}
