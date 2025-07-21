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

    protected $casts = [
        'job_id' => 'string',
        'delivery_fee' => 'float',
    ];

    /**
     * Polymorphic relationship to either orders or other order types
     */
    public function orderBelongsTo()
    {
        return $this->morphTo();
    }
}
