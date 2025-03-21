<?php

namespace App\Models;

use App\Categories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_category_id',
    ];
    /**
     * Relations
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_category_id');
    }
    /**
     * Helpers
     */
    public static function add(string $name, int $parentCategoryId): SubCategory
    {
        return self::create([
            'name' => $name,
            'parent_category_id' => $parentCategoryId
        ]);
    }

    public static function updateInfo(
        int $id,
        ?string $name = null
    ): bool {
        $subCategory = self::findOrFail($id);

        if (!is_null($name)) $subCategory->name = $name;

        return $subCategory->save();
    }
}
