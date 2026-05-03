<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

class VanInventoryOrder extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relations
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(VanInventoryOrderItem::class);
    }

    /**
     * Helpers
     */
    public static function updateOrderStatus(int $id, OrderStatusEnum $status): int
    {
        return self::where('id', '=', $id)->update([
            'order_status' => $status->value,
        ]);
    }

    public static function add(
        int $companyId,
        int $sellerId,
        int $vanId,
        float $orderTotal,
        OrderTypeEnum $type,
        float $customerLat,
        float $customerLon,
        string $countryCode,
        string $phoneNumber,
        string $address,
        string $country,
        string $state,
        string $city,
        string $postcode,
        ?string $customerName = null,
    ): self {
        return self::create([
            'company_id' => $companyId,
            'seller_id' => $sellerId,
            'van_id' => $vanId,
            'order_total' => $orderTotal,
            'order_status' => OrderStatusEnum::PENDING->value,
            'type' => $type->value,
            'customer_name' => $customerName,
            'customer_lat' => $customerLat,
            'customer_lon' => $customerLon,
            'country_code' => $countryCode,
            'phone_number' => $phoneNumber,
            'address' => $address,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'postcode' => $postcode,
        ]);
    }

    public static function getById(int $id, array $columns = ['*']): VanInventoryOrder
    {
        return self::select($columns)
            ->with(['seller', 'buyer', 'orderItems.product'])
            ->where('id', '=', $id)
            ->firstOrFail();
    }

    public static function getAll(
        OrderByEnum $orderBy,
        ?int $vanInventoryOrderId = null,
        ?int $companyId = null,
        ?int $sellerId = null,
        int $perPage = 10,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->with(['orderItems.product'])
            ->when($vanInventoryOrderId, function ($query) use ($vanInventoryOrderId) {
                $query->where('id', '=', $vanInventoryOrderId);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', '=', $companyId);
            })
            ->when($sellerId, function ($query) use ($sellerId) {
                $query->where('seller_id', '=', $sellerId);
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate($perPage);
    }

    public static function getOrdersCount(?int $companyId = null, ?OrderStatusEnum $orderStatus = null): int
    {
        return self::when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', '=',$companyId);
            })
            ->when($orderStatus, function ($query) use ($orderStatus) {
                $query->where('order_status', '=', $orderStatus->value);
            })
            ->count();
    }

}
