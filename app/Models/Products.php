<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\SortByEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Products extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'product_name',
        'sku',
        'price',
        'discount_percentage',
        'weight',
        'brand',
        'size',
        'status',
        'contact',
        'colors',
        'bike',
        'car',
        'van',
        'feature_img',
        'height',
        'width',
        'length',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatusEnum::class,
        ];
    }

    /**
     * Laravel Built-In Helpers
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value
        );
    }

    /**
     * Scout Built-In Helpers
     */

    /**
     * Get the indexable data array for the model.
     */
    #[SearchUsingFullText(['product_name'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'seller_ids' => $this->qty()->pluck('seller_id')->toArray(),
            'category_id' => $this->category_id,
            'price' => $this->price,
            'status' => $this->status,
            'weight' => $this->weight,
            'brand' => $this->brand,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatusEnum::ENABLE;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'sellers:id,business_name,business_hours,full_address,country,state,city,lat,lon',
            'qty:id,product_id,qty',
            'images:id,product_id,product_image',
            'category:id,category_name,category_image',
        ]);
    }

    /**
     *  Define filterable attributes for meilisearch
     */
    public function scoutFilterable(): array
    {
        return [
            'id',
            'product_name',
            'seller_id',
            'category_id',
            'price',
            'status',
            'weight',
            'brand',
        ];
    }

    /**
     * Relations
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Fetch all sellers related to a product.
     * "Sellers" could be parent or child sellers.
     */
    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'qty',
            'product_id',
            'seller_id',
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function rattings(): HasMany
    {
        return $this->hasMany(Rattings::class, 'product_id');
    }

    public function quantities(): HasMany
    {
        return $this->hasMany(Qty::class, 'product_id');
    }

    public function qty(): HasMany
    {
        return $this->quantities();
    }

    /**
     * Scopes
     */
    public function scopeParentSellerProducts(Builder $query): void
    {
        $query->select(
            'products.id as prod_id',
            'products.seller_id as parent_seller_id',
            'products.category_id',
            'products.product_name',
            'products.price',
            'products.feature_img',
        );
    }

    public function scopeChildSellerQty(Builder $query, int $childSellerId): void
    {
        $query->leftJoin('qty', function ($join) use ($childSellerId) {
            $join->on('qty.product_id', '=', 'products.id')->where('qty.seller_id', '=', $childSellerId);
        })->select(
            'products.id as prod_id',
            'products.seller_id as parent_seller_id',
            'products.category_id',
            'products.product_name',
            'products.price',
            'products.feature_img',
            'qty.id as qty_id',
            'qty.seller_id as child_seller_id',
            'qty.qty'
        );
    }

    public function scopeWhereProductIsEnable(Builder $query): void
    {
        $query->where('status', ProductStatusEnum::ENABLE);
    }

    /**
     * Helpers
     */
    public static function getCommonColors(): array
    {
        return [
            'Blue',
            'Green',
            'Red',
            'Yellow',
            'White',
            'Black',
            'Orange',
            'Pink',
            'Brown',
            'Indigo',
            'Purple',
            'Gray',
            'Silver',
        ];
    }

    public static function getCommonColumns(): array
    {
        return [
            'id',
            'seller_id as parent_seller_id',
            'category_id',
            'product_name',
            'sku',
            'price',
            'featured',
            'discount_percentage',
            'weight',
            'brand',
            'size',
            'status',
            'bike',
            'car',
            'van',
            'feature_img',
            'height',
            'width',
            'length',
        ];
    }

    public static function add(array $data): Products
    {
        return self::create($data);
    }

    public static function searchProducts(
        string $productName,
        array $sellerIds,
        ?int $categoryId = null,
        ?string $sku = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?float $minWeight = null,
        ?float $maxWeight = null,
        ?string $brand = null,
        ?string $sortBy = null,
    ): array {
        $scoutData = self::search($productName)
            ->whereIn('seller_ids', $sellerIds)
            ->paginate(20, 'scoutPage')
            ->toArray();

        // $scoutData = self::search($productName)
        //     ->options([
        //         'hybrid' => [
        //             /* 50% keyword, 50% Ai */
        //             'semanticRatio' => 0.5,
        //             'embedder' => 'default'
        //         ]
        //     ])
        //     ->whereIn('seller_ids', $sellerIds)
        //     ->paginate(20, 'scoutPage')
        //     ->toArray();

        $productIds = array_column($scoutData['data'], 'id');
        unset($scoutData['data']);
        $pagination = $scoutData;
        /* Use regular Laravel query builder */
        $products = self::select(
            'products.id',
            'products.seller_id as parent_seller_id',
            'products.category_id',
            'product_name',
            'sku',
            'price',
            'featured',
            'discount_percentage',
            'weight',
            'brand',
            'size',
            'status',
            'contact',
            'colors',
            'bike',
            'car',
            'van',
            'feature_img',
            'height',
            'width',
            'length'
        )->with([
            'sellers' => function ($sellersRelation) use ($sellerIds) {
                $sellersRelation->select(
                    User::getSellerCommonColumns()
                )->whereIn('seller_id', $sellerIds);
            },
            'qty' => function ($qtyRelation) use ($sellerIds) {
                $qtyRelation->select('id', 'product_id', 'qty')->whereIn('seller_id', $sellerIds);
            },
            'images:id,product_id,product_image',
            'category:id,category_name,category_image',
        ])->when($categoryId, function ($query) use ($categoryId) {
            return $query->where('category_id', '=', $categoryId);
        })->when($brand, function ($query) use ($brand) {
            return $query->where('brand', '=', $brand);
        })->when($minPrice, function ($query) use ($minPrice) {
            return $query->where('price', '>=', $minPrice);
        })->when($maxPrice, function ($query) use ($maxPrice) {
            return $query->where('price', '<=', $maxPrice);
        })->when($minWeight, function ($query) use ($minWeight) {
            return $query->where('weight', '>=', $minWeight);
        })->when($maxWeight, function ($query) use ($maxWeight) {
            return $query->where('weight', '<=', $maxWeight);
        })->when($sku, function ($query) use ($sku) {
            return $query->where('sku', '=', $sku);
        })->when($sortBy, function ($query) use ($sortBy) {
            return match ($sortBy) {
                SortByEnum::PriceLowToHigh->value => $query->orderBy('price', 'asc'),
                SortByEnum::PriceHighToLow->value => $query->orderBy('price', 'desc'),
            };
        })->whereHas('qty', function ($qtyRelation) use ($sellerIds) {
            $qtyRelation->whereIn('seller_id', $sellerIds)
                ->whereHas('store', function ($storeQuery) {
                    $storeQuery->WhereUserIsActive();
                });
        })
            ->whereIn('products.id', $productIds)
            ->get();

        return ['data' => $products, 'pagination' => $pagination];

        /* Old search query which is using only 1 query for all conditions */
        // return self::search($productName)
        //     ->query(
        //         fn($query) => $query->select(
        //             'products.id',
        //             'products.seller_id as parent_seller_id',
        //             'products.category_id',
        //             'product_name',
        //             'sku',
        //             'price',
        //             'featured',
        //             'discount_percentage',
        //             'weight',
        //             'brand',
        //             'size',
        //             'status',
        //             'contact',
        //             'colors',
        //             'bike',
        //             'car',
        //             'van',
        //             'feature_img',
        //             'height',
        //             'width',
        //             'length'
        //         )->with([
        //             'sellers' => function ($sellerRelation) use ($sellerIds) {
        //                 $sellerRelation->select(
        //                     'users.id',
        //                     'business_name',
        //                     'business_hours',
        //                     'full_address',
        //                     'country',
        //                     'state',
        //                     'city',
        //                     'lat',
        //                     'lon'
        //                 )->whereIn('seller_id', $sellerIds);
        //             },
        //             'qty' => function ($qtyRelation) use ($sellerIds) {
        //                 $qtyRelation->select('id', 'product_id', 'seller_id', 'qty')->whereIn('seller_id', $sellerIds);
        //             },
        //             'images:id,product_id,product_image',
        //             'category:id,category_name,category_image'
        //         ])->whereHas('qty', function ($qtyQuery) use ($sellerIds) {
        //             $qtyQuery->whereIn('seller_id', $sellerIds)
        //                 ->whereHas('store', function ($storeQuery) {
        //                     $storeQuery->WhereUserIsActive();
        //                 });
        //         })->when($categoryId, function ($query) use ($categoryId) {
        //             return $query->where('category_id', $categoryId);
        //         })->when($brand, function ($query) use ($brand) {
        //             return $query->where('brand', $brand);
        //         })->when($minPrice, function ($query) use ($minPrice) {
        //             return $query->where('price', '>=', $minPrice);
        //         })->when($maxPrice, function ($query) use ($maxPrice) {
        //             return $query->where('price', '<=', $maxPrice);
        //         })->when($minWeight, function ($query) use ($minWeight) {
        //             return $query->where('weight', '>=', $minWeight);
        //         })->when($maxWeight, function ($query) use ($maxWeight) {
        //             return $query->where('weight', '<=', $maxWeight);
        //         })
        //     )
        //     ->paginate(20);
    }

    public static function getAllProducts(): LengthAwarePaginator
    {
        return self::with([
            'qty:id,product_id,qty',
            'images:id,product_id,product_image',
            'category:id,category_name,category_image',
        ])
            ->whereHas('store', function ($query) {
                $query->where('is_active', 1);
            })
            ->paginate(20);
    }

    public static function getProductsInfoByCategoryId(int $categoryId, int $sellerId, array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        User::getSellerCommonColumns()
                    )->where('seller_id', '=', $sellerId);
                },
                'qty' => function ($qtyRelation) use ($sellerId) {
                    $qtyRelation->select('id', 'product_id', 'qty')->where('seller_id', '=', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image',
            ])
            ->whereHas('qty', function ($qtyRelation) use ($sellerId, $categoryId) {
                $qtyRelation->where('seller_id', '=', $sellerId)
                    ->where('category_id', '=', $categoryId);
            })
            ->WhereProductIsEnable()
            ->paginate(20);
    }

    public static function getProductsInfoBySellerId(int $sellerId, array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        User::getSellerCommonColumns()
                    )->where('seller_id', '=', $sellerId);
                },
                'qty' => function ($qtyRelation) use ($sellerId) {
                    $qtyRelation->select('id', 'product_id', 'qty')->where('seller_id', '=', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image',
            ])
            ->whereHas('qty', function ($qtyRelation) use ($sellerId) {
                $qtyRelation->where('seller_id', '=', $sellerId);
            })
            ->WhereProductIsEnable()
            ->paginate(20);
    }

    public static function getProductInfoEvenDisabled(int $sellerId, int $productId, array $columns = ['*']): Products
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        User::getSellerCommonColumns()
                    )->where('seller_id', '=', $sellerId);
                },
                'qty' => function ($qtyRelation) use ($sellerId) {
                    $qtyRelation->select('id', 'product_id', 'qty')->where('seller_id', '=', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image',
            ])
            ->whereHas('qty', function ($qtyRelation) use ($sellerId) {
                $qtyRelation->where('seller_id', '=', $sellerId);
            })
            ->where('id', '=', $productId)
            ->firstOrFail();
    }

    public static function getProductInfoWithRelations(int $sellerId, int $productId, array $columns = ['*']): Products
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        User::getSellerCommonColumns()
                    )->where('seller_id', '=', $sellerId);
                },
                'qty' => function ($qtyRelation) use ($sellerId) {
                    $qtyRelation->select('id', 'product_id', 'qty')->where('seller_id', '=', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image',
            ])
            ->whereHas('qty', function ($qtyRelation) use ($sellerId) {
                $qtyRelation->where('seller_id', '=', $sellerId);
            })
            ->where('id', '=', $productId)
            ->WhereProductIsEnable()
            ->firstOrFail();
    }

    public static function getProductInfoWithoutRelationsById(int $id, array $columns = ['*']): Products
    {
        return self::select($columns)->where('id', '=', $id)->WhereProductIsEnable()->first();
    }

    public static function getParentSellerProducts(int $seller_id): LengthAwarePaginator
    {
        return self::WhereProductIsEnable()->where('seller_id', '=', $seller_id)->paginate(20);
    }

    public static function getParentSellerProductsAsc(int $seller_id): Collection
    {
        return self::WhereProductIsEnable()->where('seller_id', '=', $seller_id)->orderBy('id', 'asc')->get();
    }

    public static function getParentSellerProductsForView(
        int $sellerId,
        string $search = '',
        ?int $categoryId = null,
        string $orderBy = 'desc'
    ): LengthAwarePaginator {
        return self::with('category')
            ->withAvg('rattings:ratting', 'average_ratting')
            ->where('product_name', 'LIKE', "%{$search}%")
            ->where('seller_id', '=', $sellerId)
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', '=', $categoryId);
            })
            ->orderBy('id', $orderBy)
            ->paginate(12);
    }

    public static function getChildSellerProductsForView(
        int $childSellerId,
        string $search = '',
        ?int $categoryId = null
    ): LengthAwarePaginator {
        $parentSellerId = User::find($childSellerId)->parent_store_id;
        $qty = Qty::where('seller_id', '=', $childSellerId)->first();

        $query = (empty($qty)) ? self::ParentSellerProducts() : self::ChildSellerQty(childSellerId: $childSellerId);

        return $query->where('products.product_name', 'LIKE', "%{$search}%")
            ->where('products.seller_id', $parentSellerId)
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', '=', $categoryId);
            })
            ->paginate(20);
    }

    public static function getParentOrChildSellerProductsForView(
        int $sellerId,
        ?string $search = null,
        ?int $categoryId = null,
        ?ProductStatusEnum $status = null,
        string $orderBy = 'desc'
    ): LengthAwarePaginator {
        return self::select('products.*')
            ->join('qty', function ($join) use ($sellerId) {
                $join->on('qty.product_id', '=', 'products.id')
                    ->where('qty.seller_id', '=', $sellerId)
                    ->whereNull('qty.deleted_at');
            })
            ->with('category')
            ->withAvg('rattings:ratting', 'average_ratting')
            ->when($search, function ($query, $search) {
                return $query->where('products.product_name', 'LIKE', "%{$search}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('products.category_id', '=', $categoryId);
            })
            ->when($status, function ($query, $status) {
                return $query->where('products.status', '=', $status);
            })
            ->distinct()
            ->orderBy('products.id', $orderBy)
            ->paginate(12);
    }

    public static function getProductsByParameters(int $seller_id, string $sku, int $catgory_id): Products
    {
        return self::where('seller_id', '=', $seller_id)
            ->where('sku', '=', $sku)
            ->where('category_id', '=', $catgory_id)
            ->first();
    }

    public static function getProductWeight(int $id): float
    {
        // $product = self::select('weight')->where('id', '=', $id)->first()->weight;

        // return $product->weight;

        return self::select('weight')->where('id', '=', $id)->first()->weight;
    }

    public static function getProductVolume(int $id): float
    {
        return self::select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', '=', $id)
            ->first()
            ->volumn;
    }

    public static function getProductPrice(int $id): float
    {
        $product = self::find($id);
        /* Due to some unknown reason this line was previously written for getting discounted price */
        // return ($product->discount_percentage > 0) ? $product->discount_percentage * 1.2 : $product->price * 1.2;
        return ($product->discount_percentage > 0) ?
            $product->price - ($product->price * ($product->discount_percentage / 100)) :
            $product->price;
    }

    public static function getFeaturedProducts(int $seller_id): LengthAwarePaginator
    {
        return self::whereHas('store', function ($query) {
            $query->where('is_active', 1);
        })->where('seller_id', '=', $seller_id)
            ->where('featured', '=', 1)
            ->WhereProductIsEnable()
            ->orderByDesc('id')
            ->paginate(10);
    }

    public static function getActiveProducts(): LengthAwarePaginator
    {
        return self::whereHas('store', function ($query) {
            $query->WhereUserIsActive();
        })->WhereProductIsEnable()
            ->paginate(10);
    }

    public static function getProductsByLocation(object $request): LengthAwarePaginator
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');

        return self::selectRaw('*, ( 6367 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->paginate(10);
    }

    public static function getBulkProducts(object $request): LengthAwarePaginator
    {
        $ids = explode(',', $request->ids);

        return self::whereIn('id', $ids)->paginate(10);
    }

    /**
     * SAP == Search Alternative Product
     */
    public static function getProductsForSAPModal(int $sellerId, string $search = ''): Paginator
    {
        if (! empty($search)) {
            $search = str_replace(' ', '%', $search);
        }

        return self::join('qty', 'products.id', '=', 'qty.product_id')
            ->select('products.id as prod_id', 'products.product_name', 'qty.qty', 'products.price')
            ->where('qty.seller_id', '=', $sellerId)
            // ->where('products.seller_id', '=', $sellerId)
            ->when($search, function ($query, $search) {
                return $query->where('products.product_name', 'LIKE', "%{$search}%");
            })
            ->simplePaginate(5, ['*'], 'sap_products_page');
    }

    public static function markAsFeatured(int $id, int $status): int
    {
        return self::where('id', '=', $id)
            ->where('seller_id', '=', User::getAuthUser()->id)
            ->update([
                'featured' => $status,
            ]);
    }

    public static function toggleProduct(int $id, string $status): int
    {
        return self::where('id', '=', $id)
            ->update([
                'status' => $status,
            ]);
    }

    public static function toggleAllProducts(string $status): int
    {
        return self::where('seller_id', '=', User::getAuthUser()->id)
            ->update([
                'status' => $status,
            ]);
    }
}
