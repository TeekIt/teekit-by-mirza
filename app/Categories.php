<?php

namespace App;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Model;
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

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['subCategories'];
    /**
     * Relations
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'parent_category_id')
            ->select(
                'id',
                'name',
                'parent_category_id',
            );
    }

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
