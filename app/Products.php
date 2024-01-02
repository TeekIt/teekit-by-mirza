<?php

namespace App;

use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Products extends Model
{
    use Searchable;

    protected $fillable = ['*'];
    /**
     * Built-In Helpers
     */
    public function toSearchableArray(): array
    {
        return [
            'id' =>  $this->id,
            'product_name' => $this->product_name,
            'user_id' => $this->user_id,
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
        return ['id', 'product_name', 'user_id', 'category_id', 'price', 'status', 'weight', 'brand'];
    }
    /**
     * Relations
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->hasMany(Qty::class);
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
    public static function searchProducts(string $product_name, ?array $store_ids, ?int $category_id, ?int $store_id, ?string $brand, ?float $min_price, ?float $max_price, ?float $min_weight, ?float $max_weight): object
    {
        return self::search($product_name)
            ->query(fn ($query) => $query->with(['store', 'quantities', 'images', 'category']))
            ->where('status', 1)
            ->when($store_ids, function ($query) use ($store_ids) {
                return $query->where_in('user_id', $store_ids['ids']);
            })
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })
            ->when($store_id, function ($query) use ($store_id) {
                return $query->where('user_id', $store_id);
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

    public static function getAllProducts(): object
    {
        return self::with([
            'quantities',
            'images',
            'category'
        ])
            ->whereHas('store', function ($query) {
                $query->where('is_active', 1);
            })
            ->paginate(20);
    }

    public static function getProductsInfoBySellerId(int $seller_id): object
    {
        return self::with([
            'quantities' => function ($query) use ($seller_id) {
                $query->where('users_id', $seller_id);
            },
            'images',
            'category'
        ])
            ->whereHas('quantities', function ($query) use ($seller_id) {
                $query->where('users_id', $seller_id);
            })
            ->paginate(20);
    }

    public static function getProductInfo(int $seller_id, int $product_id, array $columns): object
    {
        $product = self::select($columns)
        ->with([
            'quantities' => function ($query) use ($seller_id) {
                $query->where('users_id', $seller_id);
            },
            'images:id,product_id,product_image',
            'category:id,category_name,category_image'
        ])
            ->whereHas('quantities', function ($query) use ($seller_id) {
                $query->where('users_id', $seller_id);
            })
            ->where('id', $product_id)
            ->first();

        $product->qty = $product->quantities[0]->qty;
        $product->store = User::getUserByID($seller_id, ['business_name', 'business_hours']);
        unset($product->quantities);

        return $product;
    }

    public static function getOnlyProductDetailsById(int $product_id): object
    {
        return self::where('id', $product_id)
            ->where('status', '1')
            ->first();
    }

    public static function getParentSellerProducts(int $seller_id): object
    {
        return self::where('user_id', '=', $seller_id)->where('status', '=', 1)->paginate(20);
    }

    public static function getParentSellerProductsAsc(int $seller_id): object
    {
        return self::where('user_id', '=', $seller_id)->where('status', '=', 1)->orderBy('id', 'asc')->get();
    }

    public static function getParentSellerProductsDescForView(int $seller_id, string $search = '', int $category_id = null): object
    {
        return self::with('category')
            ->withAvg('rattings:ratting', 'average_ratting')
            ->where('product_name', 'LIKE', "%{$search}%")
            ->where('user_id', '=', $seller_id)
            ->when($category_id, function ($query, $category_id) {
                return $query->where('category_id', '=', $category_id);
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public static function getChildSellerProductsForView(int $child_seller_id, string $search = '', int $category_id = null): object
    {
        $parent_seller_id = User::find($child_seller_id)->parent_store_id;
        $qty = Qty::where('users_id', $child_seller_id)->first();
        if (!empty($qty)) {
            return self::join('qty', 'products.id', '=', 'qty.products_id')
                ->select('products.id as prod_id', 'products.user_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.users_id as child_seller_id', 'qty.qty')
                ->where('products.product_name', 'LIKE', "%{$search}%")
                ->where('products.user_id', $parent_seller_id)
                ->where('qty.users_id', $child_seller_id)
                ->when($category_id, function ($query, $category_id) {
                    return $query->where('category_id', '=', $category_id);
                })
                ->paginate(20);
        } else {
            // return [
            //     'data' => self::join('qty', 'products.id', '=', 'qty.products_id')
            //         ->select('products.id as prod_id', 'products.user_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.qty')
            //         ->where('products.product_name', 'LIKE', "%{$search}%")
            //         ->where('products.user_id', $parent_seller_id)
            //         ->where('qty.users_id', $parent_seller_id)
            //         ->when($category_id, function ($query, $category_id) {
            //             return $query->where('category_id', '=', $category_id);
            //         })
            //         ->paginate(20),
            //     'owner' => 'parent'
            // ];
            return self::join('qty', 'products.id', '=', 'qty.products_id')
                ->select('products.id as prod_id', 'products.user_id as parent_seller_id', 'products.category_id', 'products.product_name', 'products.price', 'products.feature_img', 'qty.id as qty_id', 'qty.qty')
                ->where('products.product_name', 'LIKE', "%{$search}%")
                ->where('products.user_id', $parent_seller_id)
                ->where('qty.users_id', $parent_seller_id)
                ->when($category_id, function ($query, $category_id) {
                    return $query->where('category_id', '=', $category_id);
                })
                ->paginate(20);
        }
    }

    public function getProductsByParameters(int $store_id, string $sku, int $catgory_id): object
    {
        return self::where('user_id', '=', $store_id)
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

    public static function getProductPrice(int $product_id)
    {
        $product = self::find($product_id);
        if ($product->discount_percentage > 0) return $product->discount_percentage * 1.2;
        return $product->price * 1.2;
    }

    public static function getFeaturedProducts(int $store_id): object
    {
        return self::whereHas('store', function ($query) {
            $query->where('is_active', 1);
        })->where('user_id', '=', $store_id)
            ->where('featured', '=', 1)
            ->where('status', '=', 1)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public static function getActiveProducts(): object
    {
        return self::whereHas('store', function ($query) {
            $query->where('is_active', 1);
        })->where('status', 1)
            ->paginate();
    }

    public static function getProductsByLocation(object $request): object
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        return self::selectRaw('*, ( 6367 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->paginate();
    }

    public static function getBulkProducts(object $request): object
    {
        $ids = explode(',', $request->ids);
        return self::query()->whereIn('id', $ids)->paginate();
    }
    // SAP == Search Alternative Product
    public static function getProductsForSAPModal(int $seller_id, string $search = ''): object
    {
        if (!empty($search)) $search = str_replace(' ', '%', $search);
        return self::join('qty', 'products.id', '=', 'qty.products_id')
            ->select('products.id as prod_id', 'products.product_name', 'qty.qty', 'products.price')
            ->where('qty.users_id', $seller_id)
            ->where('products.user_id', $seller_id)
            ->when($search, function ($query, $search) {
                return $query->where('products.product_name', 'LIKE', "%{$search}%");
            })
            ->simplePaginate(5, ['*'], 'sap_products_page');
    }

    public static function markAsFeatured(int $id, int $status)
    {
        return self::where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'featured' => $status
            ]);
    }

    public static function toggleProduct(int $id, int $status)
    {
        return self::where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }

    public static function toggleAllProducts(int $status)
    {
        return self::where('user_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }
}
