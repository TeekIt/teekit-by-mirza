<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VanOperativeProductUsage extends Model
{
     use HasFactory, SoftDeletes;
    protected $table = 'van_operative_product_usages';
    /**
     * Fields protected from mass assignment
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Attribute type casting
     *
     * @var array
     */
    protected $casts = [
        'quantity_used' => 'integer',
        'used_at'       => 'datetime',
    ];

    /**
     * Relationship: Usage belongs to Van
     */
    public function van()
    {
        return $this->belongsTo(Van::class);
    }

    /**
     * Relationship: Usage belongs to Van Product
     */
    public function vanProduct()
    {
        return $this->belongsTo(VanProduct::class, 'van_product_id');
    }
    /**
     * Get usage history for a specific van
     *
     * @param int $vanId
     * @return \Illuminate\Support\Collection
     */
    public static function getHistoryByVan(int $vanId)
    {
        return self::where('van_id', $vanId)
            ->orderBy('used_at', 'desc')
            ->with(['van:id,operative'])
            ->get();
    }
/**
     * Add a new usage record
     *
     * @param array $data
     * @return self
     */
    public static function add(array $data): self
        {
            return self::create([
                'van_id'        => $data['vanId'],
                'van_product_id'=> $data['productId'],
                'quantity_used' => $data['quantityUsed'],
                'job_reference' => $data['jobReference'],
                'used_at'       => $data['timestamp'] ?? now(),
            ]);
        }

    
}
