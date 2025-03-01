<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Products;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

class Categories extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_name',
        'category_image',
    ];
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

    public static function add(string $categoryName, string $categoryImage): Categories
    {
        return self::create([
            'category_name' => $categoryName,
            'category_image' => $categoryImage,
        ]);
    }

    public static function updateInfo(
        int $id,
        ?string $categoryName = null,
        ?string $categoryImage = null
    ): bool {
        $category = self::findOrFail($id);

        if (!is_null($categoryName)) $category->category_name = $categoryName;
        if (!is_null($categoryImage)) $category->category_image = $categoryImage;

        return $category->save();
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

    public static function getAllCategoriesBySellerId(int $sellerId, array $columns): Collection
    {
        return self::select($columns)
            ->whereHas('qty', function ($qtyQuery) use ($sellerId) {
                $qtyQuery->where('seller_id', $sellerId);
            })->get();
    }

    public static function getCategoriesForView(array $columns = ['*'], string $orderBy = 'desc', int $perPage = 10): LengthAwarePaginator
    {
        return self::select($columns)
            ->orderBy('created_at', $orderBy)
            ->paginate($perPage);
    }

    public static function allCategories(array $columns = ['*']): Collection
    {
        return self::all($columns);
    }
}
