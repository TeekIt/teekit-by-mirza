<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StuartDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'job_id',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    /**
     * Relations
     */
    //

    /**
     * Helpers
     */
    public static function insertInfo(int $orderId, int $jobId): StuartDelivery
    {
        return self::create([
            'order_id' => $orderId,
            'job_id' => $jobId,
        ]);
    }
}
