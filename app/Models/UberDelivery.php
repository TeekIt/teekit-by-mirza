<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    protected function casts(): array
    {
        return [
            'job_id' => 'string',
            'delivery_fee' => 'float',
        ];
    }

    /**
     * Relations
     */
    /* Polymorphic relationship to either 'orders' table or 'orders_from_other_sellers' table */
    public function orderBelongsTo(): MorphTo
    {
        return $this->morphTo();
    }
}
