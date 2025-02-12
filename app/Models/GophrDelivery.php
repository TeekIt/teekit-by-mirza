<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GophrDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_belongs_to_type',
        'order_belongs_to_id',
        'job_id',
    ];
    /**
     * Relations
     */
    // 

    /**
     * Helpers
     */
    public static function getByOrderId(
        string $orderBelongsToType,
        int $orderBelongsToId,
        array $columns = ['*']
    ): GophrDelivery {
        return self::select($columns)
            ->where('order_belongs_to_type', $orderBelongsToType)
            ->where('order_belongs_to_id', $orderBelongsToId)
            ->first();
    }

    public static function add(
        string $orderBelongsToType,
        int $orderBelongsToId,
        string $jobId
    ): GophrDelivery {
        return self::create([
            'order_belongs_to_type' => $orderBelongsToType,
            'order_belongs_to_id' => $orderBelongsToId,
            'job_id' => $jobId,
        ]);
    }
}
