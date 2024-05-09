<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StuartDelivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'job_id'
    ];
    /**
     * Relations
     */
    // 

    /**
     * Helpers
     */
    public static function insertInfo(int $order_id, int $job_id): StuartDelivery
    {
        return self::create([
            'order_id' => $order_id,
            'job_id' => $job_id
        ]);
    }
}
