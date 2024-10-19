<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_token',
    ];
    /**
     * Relations
     */
    // 

    /**
     * Helpers
     */
    public static function addOrUpdate(?int $userId, string $deviceId, string $deviceToken): self
    {
        return self::updateOrCreate(
            ['device_id' => $deviceId],
            [
                'user_id' => $userId,
                'device_token' => $deviceToken
            ]
        );
    }
}
