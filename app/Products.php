<?php

namespace App;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Products extends Model
{
    use Searchable, HasFactory, SoftDeletes;

    protected $fillable = ['*'];
    /**
     * Built-In Helpers
     */
    public function toSearchableArray(): array
    {
        return [
            'id' =>  $this->id,
            'product_name' => $this->product_name,
            'seller_id' => $this->seller_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'status' => $this->status,
            'wieght' => $this->weight,
            'brand' => $this->brand
        ];
    }
    // Define filterable attributes for meilisearch
    public function scoutFilterable(): array
    {
        return ['id', 'product_name', 'seller_id', 'category_id', 'price', 'status', 'weight', 'brand'];
    }
    /**
     * Relations
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
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
     * Helpers
     */
    public static function searchProducts(
        string $product_name,
        ?array $seller_ids,
        ?int $category_id,
        ?int $seller_id,
        ?string $brand,
        ?float $min_price,
        ?float $max_price,
        ?float $min_weight,
        ?float $max_weight
    ): LengthAwarePaginator {
        return self::search($product_name)
            ->query(fn($query) => $query->with([
                'store:id,business_name,business_hours,full_address,country,state,city,lat,lon',
                'qty:id,product_id,qty',
                'images:id,product_id,product_image',
                'category:id,category_name,category_image'
            ]))
            ->where('status', '1')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                return $query->where_in('seller_id', $seller_ids['ids']);
            })
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })
            ->when($seller_id, function ($query) use ($seller_id) {
                return $query->where('seller_id', $seller_id);
            })
            ->when($brand, function ($query) use ($brand) {
                return $query->where('brand', $brand);
            })
            ->when($min_price, function ($query) use ($min_price) {
                return $query->where('price', '>=', $min_price);
            })
            ->when($max_price, function ($query) use ($max_price) {
                return $query->where('price', '<=', $max_price);
            })
            ->when($min_weight, function ($query) use ($min_weight) {
                return $query->where('weight', '>=', $min_weight);
            })
            ->when($max_weight, function ($query) use ($max_weight) {
                return $query->where('weight', '<=', $max_weight);
            })
            ->paginate(20);
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
            ->where('status', '1')
            ->paginate(10);
    }

    public static function getProductsInfoBySellerId(int $seller_id): LengthAwarePaginator
    {
        return self::with([
            'qty' => function ($query) use ($seller_id) {
                $query->select('id', 'product_id', 'qty')->where('seller_id', $seller_id);
            },
            'images:id,product_id,product_image',
            'category:id,category_name,category_image'
        ])
            ->whereHas('qty', function ($query) use ($seller_id) {
                $query->where('seller_id', $seller_id);
            })
            ->where('status', '1')
            ->paginate(20);
    }

    public static function getProductInfo(int $seller_id, int $product_id, array $columns): Products
    {
        $product = self::select($columns)
            ->with([
                'qty' => function ($query) use ($seller_id) {
                    $query->select('id', 'product_id', 'qty')->where('seller_id', $seller_id);
                },
                'images:id,product_id,product_image',
                'category:id,category_name,category_image'
            ])
            ->whereHas('qty', function ($query) use ($seller_id) {
                $query->where('seller_id', $seller_id);
            })
            ->where('id', $product_id)
            ->where('status', '1')
            ->first();

        // $product->qty = $product->quantities[0]->qty;
        $product->store = User::getUserByID($seller_id, ['id', 'business_name', 'business_hours', 'full_address', 'country', 'state', 'city', 'lat', 'lon', 'user_img']);
        // unset($product->quantities);

        return $product;
    }

    public static function getOnlyProductDetailsById(int $product_id): Products
    {
        return self::where('id', $product_id)
            ->where('status', '1')
            ->first();
    }

    public static function getParentSellerProducts(int $seller_id): LengthAwarePaginator
    {
        return self::where('seller_id', '=', $seller_id)->where('status', '=', '1')->paginate(20);
    }

    public static function getParentSellerProductsAsc(int $seller_id): Collection
    {
        return self::where('seller_id', '=', $seller_id)->where('status', '=', '1')->orderBy('id', 'asc')->get();
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
        if (!empty($qty)) {
            return self::join('qty', 'products.id', '=', 'qty.product_id')
                ->select('products.id as prod_id', 'products.seller_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.seller_id as child_seller_id', 'qty.qty')
                ->where('products.product_name', 'LIKE', "%{$search}%")
                ->where('products.seller_id', $parent_seller_id)
                ->where('qty.seller_id', $child_seller_id)
                ->when($category_id, function ($query, $category_id) {
                    return $query->where('category_id', '=', $category_id);
                })
                ->paginate(20);
        } else {
            // return [
            //     'data' => self::join('qty', 'products.id', '=', 'qty.product_id')
            //         ->select('products.id as prod_id', 'products.seller_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.qty')
            //         ->where('products.product_name', 'LIKE', "%{$search}%")
            //         ->where('products.seller_id', $parent_seller_id)
            //         ->where('qty.seller_id', $parent_seller_id)
            //         ->when($category_id, function ($query, $category_id) {
            //             return $query->where('category_id', '=', $category_id);
            //         })
            //         ->paginate(20),
            //     'owner' => 'parent'
            // ];
            return self::join('qty', 'products.id', '=', 'qty.product_id')
                ->select('products.id as prod_id', 'products.seller_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.qty')
                ->where('products.product_name', 'LIKE', "%{$search}%")
                ->where('products.seller_id', $parent_seller_id)
                ->where('qty.seller_id', $parent_seller_id)
                ->when($category_id, function ($query, $category_id) {
                    return $query->where('category_id', '=', $category_id);
                })
                ->paginate(20);
        }
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
            ->where('status', '=', '1')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public static function getActiveProducts(): LengthAwarePaginator
    {
        return self::whereHas('store', function ($query) {
            $query->where('is_active', 1);
        })->where('status', '1')
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
        if (!empty($search)) $search = str_replace(' ', '%', $search);
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
