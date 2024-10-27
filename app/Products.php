<?php

namespace App;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Scout\Attributes\SearchUsingFullText;

class Products extends Model
{
    use Searchable, HasFactory, SoftDeletes;

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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => ProductStatus::class
    ];
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
     *
     * @return array
     */
    #[SearchUsingFullText(['product_name'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'seller_id' => $this->seller_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'status' => $this->status,
            'wieght' => $this->weight,
            'brand' => $this->brand
        ];
    }
    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::ENABLE;
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
            'brand'
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
        return $this->hasMany(productImages::class, 'product_id');
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
     * Validators
     */
    public static function validator(Request $request): object
    {
        return Validator::make($request->all(), [
            'category_id' => 'required',
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'lat' => 'required|string|max:255',
            'lon' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'qty' => 'required|string|max:255'
        ]);
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

    public function scopeChildSellerQty(Builder $query, int $child_seller_id): void
    {
        $query->leftJoin('qty', function ($join) use ($child_seller_id) {
            $join->on('qty.product_id', '=', 'products.id')->where('qty.seller_id', '=', $child_seller_id);
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
        $query->where('status', ProductStatus::ENABLE);
    }
    /**
     * Helpers
     */
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
            'length'
        ];
    }

    public static function add(array $data): Products
    {
        return self::create($data);
    }

    public static function searchProducts(
        string $productName,
        array $sellerIds,
        ?int $categoryId,
        // ?array $oldSellerIds,
        ?string $brand,
        ?float $minPrice,
        ?float $maxPrice,
        ?float $minWeight,
        ?float $maxWeight,
    ): array {
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

        $scoutData = self::search($productName)->paginate(20, 'scoutPage')->toArray();
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
            'sellers' => function ($sellerRelation) use ($sellerIds) {
                $sellerRelation->select(
                    'users.id',
                    'business_name',
                    'business_hours',
                    'full_address',
                    'country',
                    'state',
                    'city',
                    'lat',
                    'lon'
                )->whereIn('seller_id', $sellerIds);
            },
            'qty' => function ($qtyRelation) use ($sellerIds) {
                $qtyRelation->select('id', 'product_id', 'qty')->whereIn('seller_id', $sellerIds);
            },
            'images:id,product_id,product_image',
            'category:id,category_name,category_image'
        ])->whereHas('qty', function ($qtyQuery) use ($sellerIds) {
            $qtyQuery->whereIn('seller_id', $sellerIds)
                ->whereHas('store', function ($storeQuery) {
                    $storeQuery->WhereUserIsActive();
                });
        })->when($categoryId, function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })->when($brand, function ($query) use ($brand) {
            return $query->where('brand', $brand);
        })->when($minPrice, function ($query) use ($minPrice) {
            return $query->where('price', '>=', $minPrice);
        })->when($maxPrice, function ($query) use ($maxPrice) {
            return $query->where('price', '<=', $maxPrice);
        })->when($minWeight, function ($query) use ($minWeight) {
            return $query->where('weight', '>=', $minWeight);
        })->when($maxWeight, function ($query) use ($maxWeight) {
            return $query->where('weight', '<=', $maxWeight);
        })
            ->whereIn('products.id', $productIds)
            ->get();

        return ['data' => $products, 'pagination' => $pagination];
    }

    public static function getAllProducts(): LengthAwarePaginator
    {
        return self::with([
            'qty:id,product_id,qty',
            'images:id,product_id,product_image',
            'category:id,category_name,category_image'
        ])
            ->whereHas('store', function ($query) {
                $query->where('is_active', 1);
            })
            ->paginate(20);
    }

    public static function getProductsByCategoryId(int $category_id, array $columns): LengthAwarePaginator
    {
        return self::select($columns)
            ->with([
                'store:id,business_name,business_hours,full_address,country,state,city,lat,lon,user_img',
                'qty' => function ($query) use ($category_id) {
                    $query->select('id', 'product_id', 'qty')->where('category_id', $category_id);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image'
            ])->where('category_id', $category_id)
            ->WhereProductIsEnable()
            ->paginate(10);
    }

    public static function getProductsInfoBySellerId(int $sellerId, array $columns): LengthAwarePaginator
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        'users.id',
                        'business_name',
                        'business_hours',
                        'full_address',
                        'country',
                        'state',
                        'city',
                        'lat',
                        'lon'
                    )->where('seller_id', $sellerId);
                },
                'qty' => function ($query) use ($sellerId) {
                    $query->select('id', 'product_id', 'qty')->where('seller_id', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image'
            ])
            ->whereHas('qty', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->WhereProductIsEnable()
            ->paginate(20);
    }

    public static function getProductInfo(int $sellerId, int $productId, array $columns): Products
    {
        return self::select($columns)
            ->with([
                'sellers' => function ($sellersRelation) use ($sellerId) {
                    $sellersRelation->select(
                        'users.id',
                        'business_name',
                        'business_hours',
                        'full_address',
                        'country',
                        'state',
                        'city',
                        'lat',
                        'lon'
                    )->where('seller_id', $sellerId);
                },
                'qty' => function ($qtyRelation) use ($sellerId) {
                    $qtyRelation->select('id', 'product_id', 'qty')->where('seller_id', $sellerId);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image'
            ])
            ->whereHas('qty', function ($qtyRelation) use ($sellerId) {
                $qtyRelation->where('seller_id', $sellerId);
            })
            ->where('id', $productId)
            ->WhereProductIsEnable()
            ->firstOrFail();
    }

    public static function getOnlyProductDetailsById(int $product_id): Products
    {
        return self::where('id', $product_id)
            ->WhereProductIsEnable()
            ->first();
    }

    public static function getParentSellerProducts(int $seller_id): LengthAwarePaginator
    {
        return self::WhereProductIsEnable()->where('seller_id', '=', $seller_id)->paginate(20);
    }

    public static function getParentSellerProductsAsc(int $seller_id): Collection
    {
        return self::WhereProductIsEnable()->where('seller_id', '=', $seller_id)->orderBy('id', 'asc')->get();
    }

    public static function getParentSellerProductsForView(int $seller_id, string $search = '', int $category_id = null, string $order_by): LengthAwarePaginator
    {
        return self::with('category')
            ->withAvg('rattings:ratting', 'average_ratting')
            ->where('product_name', 'LIKE', "%{$search}%")
            ->where('seller_id', '=', $seller_id)
            ->when($category_id, function ($query, $category_id) {
                return $query->where('category_id', '=', $category_id);
            })
            ->orderBy('id', $order_by)
            ->paginate(12);
    }

    public static function getChildSellerProductsForView(int $child_seller_id, string $search = '', int $category_id = null): LengthAwarePaginator
    {
        $parent_seller_id = User::find($child_seller_id)->parent_store_id;
        $qty = Qty::where('seller_id', $child_seller_id)->first();

        $query = (empty($qty)) ? self::ParentSellerProducts() : self::ChildSellerQty(child_seller_id: $child_seller_id);
        return $query->where('products.product_name', 'LIKE', "%{$search}%")
            ->where('products.seller_id', $parent_seller_id)
            ->when($category_id, function ($query, $category_id) {
                return $query->where('category_id', '=', $category_id);
            })
            ->paginate(20);
    }

    public function getProductsByParameters(int $seller_id, string $sku, int $catgory_id): Products
    {
        return self::where('seller_id', '=', $seller_id)
            ->where('sku', '=', $sku)
            ->where('category_id', '=', $catgory_id)
            ->first();
    }

    public static function getProductWeight(int $product_id)
    {
        $product = self::select('weight')->where('id', $product_id)->get();
        return $product[0]->weight;
    }

    public static function getProductVolume(int $product_id)
    {
        $product = self::select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', $product_id)
            ->get();
        return $product[0]->volumn;
    }

    public static function getProductPrice(int $product_id): float
    {
        $product = self::find($product_id);
        // return ($product->discount_percentage > 0) ? $product->discount_percentage * 1.2 : $product->price * 1.2;
        return $product->price * 1.2;
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
    public static function getProductsForSAPModal(int $seller_id, string $search = ''): Paginator
    {
        if (!empty($search))
            $search = str_replace(' ', '%', $search);
        return self::join('qty', 'products.id', '=', 'qty.product_id')
            ->select('products.id as prod_id', 'products.product_name', 'qty.qty', 'products.price')
            ->where('qty.seller_id', $seller_id)
            ->where('products.seller_id', $seller_id)
            ->when($search, function ($query, $search) {
                return $query->where('products.product_name', 'LIKE', "%{$search}%");
            })
            ->simplePaginate(5, ['*'], 'sap_products_page');
    }

    public static function markAsFeatured(int $id, int $status): int
    {
        return self::where('id', $id)
            ->where('seller_id', Auth::id())
            ->update([
                'featured' => $status
            ]);
    }

    public static function toggleProduct(int $id, string $status): int
    {
        return self::where('id', $id)
            ->where('seller_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }

    public static function toggleAllProducts(string $status): int
    {
        return self::where('seller_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }
}
