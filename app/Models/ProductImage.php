<?php

namespace App\Models;

use App\Products;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_image'
    ];
    /**
     * Relations
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
    /**
     * Helpers
     */
    public static function deleteById(int $id): int
    {
        return self::find($id)->delete();
    }

    public static function add(int $id, string $imageName): ProductImage
    {
        return self::create([
            'product_id' => $id,
            'product_image' => $imageName
        ]);
    }
}
