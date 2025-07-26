<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UberDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'order_belongs_to_id',
        'order_belongs_to_type',
        'status',
        'delivery_fee',
        'delivery_details',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'job_id' => 'string',
        'delivery_fee' => 'float',
    ];
    /**
     * Relations
     */
    /* Polymorphic relationship to either 'orders' table or 'orders_from_other_sellers' table */
    public function orderBelongsTo()
    {
        return $this->morphTo();
    }
}
