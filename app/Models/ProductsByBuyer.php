<?php

namespace App\Models;

use App\Enums\TransportVehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsByBuyer extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by_type',
        'created_by_id',
        'seller_id',
        'product_name',
        'qty',
        'max_price',
        'weight',
        'brand',
        'part_number',
        'colors',
        'transport_vehicle',
        'feature_img',
        'height',
        'width',
        'length',
    ];
    /**
     * Relations
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }
    /**
     * Helpers
     */
    public static function add(
        string $createdByType,
        int $createdById,
        int $sellerId,
        string $productName,
        int $qty,
        float $maxPrice,
        ?float $weight = null,
        ?string $brand = null,
        ?string $partNumber = null,
        ?string $colors = null,
        string $transportVehicle,
        ?string $featureImg = null,
        ?float $height = null,
        ?float $width = null,
        ?float $length = null
    ): self {
        return self::create([
            'created_by_type' => $createdByType,
            'created_by_id' => $createdById,
            'seller_id' => $sellerId,
            'product_name' => $productName,
            'qty' => $qty,
            'max_price' => $maxPrice,
            'weight' => $weight,
            'brand' => $brand,
            'part_number' => $partNumber,
            'colors' => $colors,
            'transport_vehicle' => $transportVehicle,
            'feature_img' => $featureImg,
            'height' => $height,
            'width' => $width,
            'length' => $length,
        ]);
    }
}
