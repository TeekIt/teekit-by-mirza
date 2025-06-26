<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestedDelivery extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'pickup_address',
        'dropoff_address',
        'receiver_name',
        'receiver_phone',
        'package_size',
        'package_weight',
    ];
    /**
     * Helpers
     */
    public static function add(
        int $creatorId,
        string $pickupAddress,
        string $dropoffAddress,
        string $receiverName,
        int $receiverPhone,
        string $packageSize,
        string $packageWeight
    ): RequestedDelivery {
        return self::create([
            'creator_id' => $creatorId,
            'pickup_address' => $pickupAddress,
            'dropoff_address' => $dropoffAddress,
            'receiver_name' => $receiverName,
            'receiver_phone' => $receiverPhone,
            'package_size' => $packageSize,
            'package_weight' => $packageWeight
        ]);
    }

    public static function updateInfo(
        int $id,
        string $pickupAddress,
        string $dropoffAddress,
        string $receiverName,
        int $receiverPhone,
        string $packageSize,
        string $packageWeight
    ): bool {
        $delivery = self::findOrFail($id);

        $delivery->pickup_address = $pickupAddress;
        $delivery->dropoff_address = $dropoffAddress;
        $delivery->receiver_name = $receiverName;
        $delivery->receiver_phone = $receiverPhone;
        $delivery->package_size = $packageSize;
        $delivery->package_weight = $packageWeight;

        return $delivery->save();
    }
}
