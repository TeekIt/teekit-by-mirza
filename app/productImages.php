<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class productImages extends Model
{
    use HasFactory;
    
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
    public static function add(int $id, string $image_name): productImages
    {
        return self::create([
            'product_id' => $id,
            'product_image' => $image_name
        ]);
    }
}
