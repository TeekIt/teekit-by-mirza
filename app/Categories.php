<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;

class Categories extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * Relations
     */
    public function products(): HasMany
    {
        return $this->hasMany(Products::class, 'category_id', 'id');
    }

    public function qty(): HasMany
    {
        return $this->hasMany(Qty::class, 'category_id', 'id');
    }
    /**
     * Validators
     */
    public static function validator(Request $request): object
    {
        return Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:50',
        ]);
    }
    /**
     * Helpers
     */
    public static function uploadImg(object $request, string $category_name): string
    {
        $file = $request->file('category_image');
        $cat_name = str_replace(' ', '_', $category_name);
        $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
        Storage::disk('spaces')->put($filename, File::get($file));
        if (Storage::disk('spaces')->exists($filename)) { // check file exists in directory or not
            info("file is stored successfully : " . $filename);
        } else {
            info("file is not found :- " . $filename);
        }
        return $filename;
    }

    public static function add(object $request): Categories
    {
        $category = new self();
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function updateCategory(object $request, $category_id): Categories
    {
        $category = self::find($category_id);
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function getAllCategoriesByStoreId(int $store_id, array $columns): Collection
    {
        return self::select($columns)
            ->whereHas('qty', function ($query) use ($store_id) {
                $query->where('seller_id', $store_id);
            })->get();
    }

    // public static function getProducts(int $category_id): array
    // {
    //     $products = Products::getProductsByCategoryId($category_id, [
    //         'id',
    //         'seller_id',
    //         'category_id',
    //         'product_name',
    //         'sku',
    //         'price',
    //         'featured',
    //         'discount_percentage',
    //         'weight',
    //         'brand',
    //         'size',
    //         'bike',
    //         'car',
    //         'van',
    //         'feature_img',
    //         'height',
    //         'width',
    //         'length',
    //     ]);
    //     $products = $products->toArray();
    //     $data = $products['data'];

    //     $pagination = $products;
    //     unset($pagination['data']);

    //     if (!empty($products)) {
    //         return ['data' => $data, 'pagination' => $pagination];
    //     } else {
    //         return [];
    //     }
    // }

    // public static function stores(int $category_id, string $city)
    // {
    //     // Get IDs of both parent and child stores from the Qty table
    //     // $store_ids = Qty::select('seller_id')
    //     //     ->distinct()
    //     //     ->join('products', 'qty.product_id', '=', 'products.id')
    //     //     ->where('qty', '>', 0) // Products Should Be In Stock
    //     //     ->where('products.status', '=', 1) // Products Should Be Live
    //     //     ->where('qty.category_id', '=', $category_id)
    //     //     ->pluck('seller_id');

    //     // // Get active parent and child stores that have products in the specified category
    //     // return User::whereIn('id', $store_ids)
    //     // ->where('is_active', '=', 1)
    //     // ->paginate(10);

    //     // $sellers = User::join('qty', 'qty.category_id', '=', 'products.category_id')
    //     // ->join('products', 'products.id', '=', 'qty.product_id')
    //     // ->where('qty.qty', '>', 0) // Products Should Be In Stock
    //     // ->where('products.status', '=', 1) // Products Should Be Live
    //     // ->paginate(10);

    //    return  Qty::select('users.*')
    //     ->join('users', 'users.id', '=', 'qty.seller_id')
    //     ->join('products', 'products.id', '=', 'qty.product_id')
    //     ->where('qty.qty', '>', 0) // Products should be in stock
    //     ->where('qty.category_id', '=', $category_id)
    //     ->where('products.status', '=', 1) // Products should be live
    //     ->where('users.is_active', '=', 1) // Sellers should be active
    //     ->where('users.city', '=', $city)
    //     ->distinct() // Use distinct to select only unique stores
    //     ->paginate(10);

    //     // return $sellers;
    // }

    public static function allCategories(array $columns): Collection
    {
        return self::all($columns);
    }
}
