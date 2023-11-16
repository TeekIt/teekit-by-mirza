<?php

namespace App;

use Validator;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Products extends Model
{
    use Searchable;

    protected $fillable = ['*'];

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
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
    public function scoutFilterable()
    {
        return ['id', 'product_name', 'user_id', 'category_id', 'price', 'status', 'weight', 'brand'];
    }
    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quantity()
    {
        return $this->hasOne(Qty::class);
    }

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function images()
    {
        return $this->hasMany(productImages::class);
    }

    public function rattings()
    {
        return $this->hasMany(Rattings::class, 'product_id');
    }

    public function quantities()
    {
        return $this->hasMany(Qty::class);
    }
    /**
     * Validators
     */
    public static function validator(Request $request)
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

    public static function updateValidator(Request $request)
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
    public static function getProductInfo(int $product_id)
    {
        $product = Products::find($product_id);
        $product->quantity = Qty::where('users_id', '=', $product->user_id)->where('products_id', '=', $product->id)->first();
        $product->images = productImages::where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = Rattings::getRatting($product_id);
        return $product;
    }

    // public static function getProductInfoWithQty(int $product_id)
    // {
    //     $product = Products::with('quantity')
    //         ->where('id', $product_id)
    //         ->first();
    //     $product->images = productImages::where('product_id', '=', $product->id)->get();
    //     $product->category = Categories::find($product->category_id);
    //     $product->ratting = Rattings::getRatting($product_id);
    //     return $product;
    // }

    public static function getOnlyProductDetailsById(int $product_id)
    {
        return Products::where('id', $product_id)
            ->where('status', '1')
            ->first();
    }

    public static function getParentSellerProducts(int $seller_id)
    {
        return Products::where('user_id', '=', $seller_id)->where('status', '=', 1)->paginate(20);
    }

    // public static function getParentSellerNonFeaturedProducts(int $seller_id)
    // {
    //     return Products::where('user_id', '=', $seller_id)->where('featured', '=', 0)->where('status', '=', 1)->orderByDesc('id')->paginate(12);
    // }

    public static function getParentSellerProductsAsc(int $seller_id)
    {
        return Products::where('user_id', '=', $seller_id)->where('status', '=', 1)->orderBy('id', 'asc')->get();
    }

    public static function getParentSellerProductsDescForView(int $seller_id, string $search = '', int $category_id = null)
    {
        return Products::with('category', 'rattings')
            ->where('product_name', 'LIKE', "%{$search}%")
            ->where('user_id', '=', $seller_id)
            ->when($category_id, function ($query, $category_id) {
                return $query->where('category_id', '=', $category_id);
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public static function getChildSellerProductsForView(int $child_seller_id, string $search = '', int $category_id = null)
    {
        $parent_seller_id = User::find($child_seller_id)->parent_store_id;
        $qty = Qty::where('users_id', $child_seller_id)->first();
        if (!empty($qty)) {
            return Products::join('qty', 'products.id', '=', 'qty.products_id')
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
            //     'data' => Products::join('qty', 'products.id', '=', 'qty.products_id')
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
            return Products::join('qty', 'products.id', '=', 'qty.products_id')
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

    public function getProductsByParameters(int $store_id, string $sku, int $catgory_id)
    {
        return Products::where('user_id', '=', $store_id)
            ->where('sku', '=', $sku)
            ->where('category_id', '=', $catgory_id)
            ->first();
    }

    public static function getProductWeight(int $product_id)
    {
        $product = Products::select('weight')->where('id', $product_id)->get();
        return $product[0]->weight;
    }

    public static function getProductVolume(int $product_id)
    {
        $product = Products::select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', $product_id)
            ->get();
        return $product[0]->volumn;
    }

    public static function getProductPrice(int $product_id)
    {
        $product = Products::find($product_id);
        if ($product->discount_percentage > 0) return $product->discount_percentage * 1.2;
        return $product->price * 1.2;
    }

    public static function getFeaturedProducts(int $store_id)
    {
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('user_id', '=', $store_id)
            ->where('featured', '=', 1)
            ->where('status', '=', 1)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public static function getActiveProducts()
    {
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('status', 1)
        ->paginate();
    }

    public static function getProductsByLocation(object $request)
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        return Products::selectRaw('*, ( 6367 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->paginate();
    }

    public static function getBulkProducts(object $request)
    {
        $ids = explode(',', $request->ids);
        return Products::query()->whereIn('id', $ids)->paginate();
    }
    // SAP == Search Alternative Product
    public static function getProductsForSAPModal(int $seller_id, string $search = '')
    {
        if(!empty($search)) $search = str_replace(' ', '%', $search);
        return Products::join('qty', 'products.id', '=', 'qty.products_id')
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
        return Products::where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'featured' => $status
            ]);
    }

    public static function toggleProduct(int $id, int $status)
    {
        return Products::where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }

    public static function toggleAllProducts(int $status)
    {
        return Products::where('user_id', Auth::id())
            ->update([
                'status' => $status
            ]);
    }
}
