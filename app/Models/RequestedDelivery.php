<?php

namespace App\Models;

use App\Enums\DeliveryProviderEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

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
        'delivery_provider',
        'delivery_id',
        'pickup_address',
        'dropoff_address',
        'unit_address',
        'receiver_name',
        'receiver_phone',
        'receiver_email',
        'package_transport_type',
        'package_weight',
    ];
    /**
     * Helpers
     */
    public static function getForView(
        string $orderBy,
        ?string $createdAt = null,
        ?int $creatorId = null,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->when($creatorId, function ($query, $creatorId) {
                return $query->where('creator_id', '=', $creatorId);
            })
            ->when($createdAt, function ($query, $createdAt) {
                return $query->whereDate('created_at', $createdAt);
            })
            ->orderBy('created_at', $orderBy)
            ->paginate(10);
    }

    public static function add(
        int $creatorId,
        DeliveryProviderEnum $deliveryProvider,
        string $deliveryId,
        string $pickupAddress,
        string $dropoffAddress,
        ?string $unitAddress = null,
        string $receiverName,
        int $receiverPhone,
        string $receiverEmail,
        PackageTransportTypeEnum $packageTransportType,
        PackageWeightEnum $packageWeight
    ): RequestedDelivery {
        return self::create([
            'creator_id' => $creatorId,
            'delivery_provider' => $deliveryProvider,
            'delivery_id' => $deliveryId,
            'pickup_address' => $pickupAddress,
            'dropoff_address' => $dropoffAddress,
            'unit_address' => $unitAddress,
            'receiver_name' => $receiverName,
            'receiver_phone' => $receiverPhone,
            'receiver_email' => $receiverEmail,
            'package_transport_type' => $packageTransportType,
            'package_weight' => $packageWeight
        ]);
    }

    // public static function updateInfo(
    //     int $id,
    //     string $pickupAddress,
    //     string $dropoffAddress,
    //     string $receiverName,
    //     int $receiverPhone,
    //     string $packageSize,
    //     string $packageWeight
    // ): bool {
    //     $delivery = self::findOrFail($id);

    //     $delivery->pickup_address = $pickupAddress;
    //     $delivery->dropoff_address = $dropoffAddress;
    //     $delivery->receiver_name = $receiverName;
    //     $delivery->receiver_phone = $receiverPhone;
    //     $delivery->package_size = $packageSize;
    //     $delivery->package_weight = $packageWeight;

    //     return $delivery->save();
    // }
}
