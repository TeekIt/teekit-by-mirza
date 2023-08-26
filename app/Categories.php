<?php

namespace App;

use App\Http\Controllers\ProductsController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Validator;
use App\Products;

class Categories extends Model
{
    /**
     * Relations
     */
    public function products()
    {
        return $this->hasMany(Products::class, 'category_id', 'id');
    }
    /**
     * Validators
     */
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }
    /**
     * Helpers
     */
    public static function uploadImg(object $request, string $category_name)
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

    public static function add(object $request)
    {
        $category = new Categories();
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function updateCategory(object $request, $category_id)
    {
        $category = Categories::find($category_id);
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function getAllCategoriesByStoreId(int $store_id)
    {
        $categories_data = Categories::select('id as category_id', 'category_name', 'category_image', 'created_at', 'updated_at')
            ->whereHas('products', function ($query) use ($store_id) {
                $query->where('user_id', $store_id);
            })->get();

        return ($categories_data->isEmpty()) ? [] : $categories_data;
    }

    public static function allCategories()
    {
        return Categories::all();
    }

    public static function getProducts(int $category_id)
    {
        // $storeId = \request()->store_id;
        // if (!empty($storeId)) {
        //     $products = Products::whereHas('user_id', function ($query) {
        //         $query->where('is_active', 1);
        //     })
        //         ->where('category_id', $category_id)
        //         ->where('user_id', $storeId)
        //         ->where('status', 1)
        //         ->paginate();
        //     return $products;
        // }
        $products = Products::where('category_id', $category_id)
            ->where('status', 1)
            ->paginate(10);
        $pagination = $products->toArray();
        if (!$products->isEmpty()) {
            $products_data = [];
            foreach ($products as $product) $products_data[] = (new ProductsController())->getProductInfo($product->id);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function getProductsByStoreId(int $category_id, int $store_id)
    {
        $products = Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })
            ->where('category_id', $category_id)
            ->where('user_id', $store_id)
            ->where('status', 1)
            ->paginate(10);
        $pagination = $products->toArray();
        if (!$products->isEmpty()) {
            $products_data = [];
            foreach ($products as $product) $products_data[] = (new ProductsController())->getProductInfo($product->id);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function stores(int $category_id)
    {
        // Get IDs of both parent and child stores from the Qty table
        $store_ids = Qty::select('users_id')
            ->distinct()
            ->join('products', 'qty.products_id', '=', 'products.id')
            ->where('qty', '>', 0) // Products Should Be In Stock
            ->where('products.status', '=', 1) // Products Should Be Live
            ->where('qty.category_id', '=', $category_id)
            ->pluck('users_id');

        // Get active parent and child stores that have products in the specified category
        return User::whereIn('id', $store_ids)
        ->where('is_active', '=', 1) 
        ->paginate(10);
    }
}
