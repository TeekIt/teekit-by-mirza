<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VanOperativeProductUsage extends Model
{
     use HasFactory, SoftDeletes;
    protected $table = 'operative_product_usages';
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
    
}
