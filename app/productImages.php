<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class productImages extends Model
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
    public static function add(int $id, string $imageName): productImages
    {
        return self::create([
            'product_id' => $id,
            'product_image' => $imageName
        ]);
    }
}
