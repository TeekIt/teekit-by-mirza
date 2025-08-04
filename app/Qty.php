<?php

namespace App;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\ProductImage;
use Google\Service\AndroidEnterprise\Resource\Users;
use Illuminate\Database\Eloquent\Collection;

class Qty extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'qty';

    protected $fillable = [
        'seller_id',
        'product_id',
        'category_id',
        'qty',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
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
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }
    /**
     * Helpers
     */
    public static function updateQty(int $productId, int $sellerId, int $productQuantity): int
    {
        return self::where('product_id', $productId)
            ->where('seller_id', $sellerId)
            ->update(['qty' => $productQuantity]);
    }

    public static function getTotalProductsCountBySellerId(int $sellerId): int
    {
        return self::where('seller_id', '=', $sellerId)->count();
    }

    public static function syncParentSellerQuantities(int $parentSellerId, int $childSellerId): bool
    {
        return DB::statement("
            INSERT INTO `qty` (`seller_id`, `product_id`, `category_id`, `qty`)
            SELECT $childSellerId, `product_id`, `category_id`, `qty`
            FROM `qty`
            WHERE `seller_id` = $parentSellerId
        ");
    }

    public static function getSellersByGivenParams(int $categoryId, string $state): ?Collection
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
            ->where('qty.category_id', '=', $categoryId)
            ->where('products.status', '=', ProductStatusEnum::ENABLE) // Products should be live
            ->where('users.is_active', '=', User::ACTIVE) // Sellers should be active
            ->where('users.state', '=', $state)
            ->distinct() // Use distinct to select only unique stores
            ->get();
    }

    public static function getProductsByGivenIds(int $categoryId, int $sellerId): array
    {
        $paginatedData = self::select('id', 'seller_id', 'product_id', 'category_id', 'qty')
            ->with([
                'product:id,product_name,sku,price,featured,discount_percentage,weight,brand,size,bike,car,van,feature_img,height,width,length',
                'store:id,business_name,business_hours,full_address,country,state,city,lat,lon,user_img',
                'category:id,category_name,category_image',
                'productImage:id,product_id,product_image',
            ])
            ->where('category_id', $categoryId)
            ->where('seller_id', $sellerId)
            ->paginate(10);

        if (!$paginatedData->isEmpty()) {
            $productsData = $paginatedData->map(function ($singleIndex) {
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
                        'id' => $singleIndex->seller_id,
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
                        [
                            'id' => $singleIndex->id,
                            'product_id' => $singleIndex->product_id,
                            'qty' => $singleIndex->qty,
                        ]
                    ],
                    'images' => $singleIndex->productImage->map(function ($singleImage) {
                        return [
                            'id' => $singleImage->id,
                            'product_image' => $singleImage->product_image,
                        ];
                    })->toArray(),
                    'category' => [
                        'id' => $singleIndex->category_id,
                        'category_name' => $singleIndex->category->category_name,
                        'category_image' => $singleIndex->category->category_image,
                    ]
                ];
            });

            $paginatedData = $paginatedData->toArray();
            unset($paginatedData['data']);

            return ['data' => $productsData, 'pagination' => $paginatedData];
        } else {
            return [];
        }
    }

    public static function getChildSellerProducts(int $sellerId): LengthAwarePaginator
    {
        return self::where('qty.seller_id', $sellerId)
            ->join('products as prod', 'prod.id', 'qty.product_id')
            ->select('prod.*')
            ->paginate(20);
    }

    public static function subtractProductQty(int $sellerId, int $productId, int $productQuantity): int
    {
        return self::where('seller_id', $sellerId)
            ->where('product_id', $productId)
            ->decrement('qty', $productQuantity);
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
    public static function add(int $sellerId, int $productId, int $categoryId, int $productQuantity): bool
    {
        $quantity = new self();
        $quantity->seller_id = $sellerId;
        $quantity->product_id = $productId;
        $quantity->category_id = $categoryId;
        $quantity->qty = $productQuantity;

        return $quantity->save();
    }
}
