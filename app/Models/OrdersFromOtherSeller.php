<?php

namespace App\Models;

use App\Enums\ModelDisabledStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdersFromOtherSeller extends Model
{
    use HasFactory, Prunable, SoftDeletes;

    protected $fillable = ['*'];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Laravel Built-In Helpers
     */
    public function prunable()
    {
        return static::where('disabled', ModelDisabledStatusEnum::YES->value)->where('created_at', '<=', now()->addDay());
    }

    /**
     * Relations
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'product_belongs_to_type', 'product_belongs_to_id');
    }

    /**
     * Helpers
     */
    public static function updateOrderStatus(int $id, OrderStatusEnum $status): int
    {
        return self::where('id', '=', $id)->update(['order_status' => $status]);
    }

    public static function isViewed(int $id): OrdersFromOtherSeller
    {
        $order = self::findOrFail($id);
        $order->is_viewed = 1;
        $order->save();

        return $order;
    }

    public static function updateInfo(
        int $id,
        ?float $initialTotal = null,
        ?float $currentTotal = null,
        ?OrderStatusEnum $orderStatus = null
    ): bool {
        $order = self::findOrFail($id);
        if (! is_null($initialTotal)) {
            $order->initial_total = $initialTotal;
        }
        if (! is_null($currentTotal)) {
            $order->current_total = $currentTotal;
        }
        if (! is_null($orderStatus)) {
            $order->order_status = $orderStatus;
        }

        return $order->save();
    }

    public static function incrementTimesRejected(int $id): int
    {
        return self::where('id', '=', $id)->increment('times_rejected');
    }

    public static function disableThisOrderForOthers(int $parentOrderId, int $exceptSellerId): int
    {
        return self::where('parent_order_id', '=', $parentOrderId)
            ->where('seller_id', '!=', $exceptSellerId)
            ->update([
                'disabled' => ModelDisabledStatusEnum::YES->value,
            ]);
    }

    public static function moveToAnotherSeller(int $id, int $sellerId): int
    {
        return self::where('id', '=', $id)->update([
            'seller_id' => $sellerId,
            'moved_at' => now(),
        ]);
    }

    public static function add(
        string $createdByType,
        int $createdById,
        int $sellerId,
        int $parentOrderId,
        string $productBelongsToType,
        int $productBelongsToId,
        float $productPrice,
        int $productQty,
        float $initialTotal,
        ?float $customerLat,
        ?float $customerLon,
        string $receiverName,
        string $countryCode,
        string $phoneNumber,
        string $address,
        ?string $houseNo,
        ?string $flat,
        string $country,
        string $state,
        string $city,
        string $postcode,
        ?string $paymentIntentId,
        float $driverCharges,
        ?float $deliveryCharges,
        ?float $serviceCharges,
        ?string $device,
        string $type,
        ?string $description,
        string $paymentStatus,
        ?int $offloading,
        ?float $offloadingCharges,
        string $movedAt,
        string $createdAt,
    ): OrdersFromOtherSeller {
        $model = new self;
        $model->created_by_type = $createdByType;
        $model->created_by_id = $createdById;
        $model->seller_id = $sellerId;
        $model->parent_order_id = $parentOrderId;
        $model->product_belongs_to_type = $productBelongsToType;
        $model->product_belongs_to_id = $productBelongsToId;
        $model->product_price = $productPrice;
        $model->product_qty = $productQty;
        $model->initial_total = $initialTotal;
        /* When we create a new order current_total == initial_total */
        $model->current_total = $initialTotal;
        /* if ($type == OrderTypeEnum::SAME_DAY_DELIVERY->value) {
            $model->customer_lat = $customerLat;
            $model->customer_lon = $customerLon;
            $model->customer_name = $receiverName;
            $model->phone_number = $phoneNumber;
            $model->address = $address;
            $model->house_no = $houseNo;
            $model->flat = $flat;
            $model->driver_charges = $driverCharges;
            $model->delivery_charges = $deliveryCharges;
            $model->service_charges = $serviceCharges;
        } */
        $model->customer_lat = $customerLat;
        $model->customer_lon = $customerLon;
        $model->customer_name = $receiverName;
        $model->country_code = $countryCode;
        $model->phone_number = $phoneNumber;
        $model->address = $address;
        $model->house_no = $houseNo;
        $model->flat = $flat;
        $model->country = $country;
        $model->state = $state;
        $model->city = $city;
        $model->postcode = $postcode;
        $model->payment_intent_id = $paymentIntentId;

        $model->driver_charges = $driverCharges;
        $model->delivery_charges = $deliveryCharges;
        $model->service_charges = $serviceCharges;

        $model->device = $device;
        $model->type = $type;
        $model->description = $description;
        $model->payment_status = $paymentStatus;
        $model->offloading = $offloading;
        $model->offloading_charges = $offloadingCharges;
        $model->moved_at = $movedAt;
        $model->created_at = $createdAt;

        $model->save();

        return $model;
    }

    public static function getById(int $id, array $columns = ['*']): OrdersFromOtherSeller
    {
        return self::select($columns)
            ->with(['product', 'buyer', 'seller'])
            ->where('id', '=', $id)
            ->first();
    }

    public static function getForView(array $columns, int $sellerId, string $orderBy): Collection
    {
        return self::select($columns)->with('product')
            ->where('seller_id', '=', $sellerId)
            ->orderBy('created_at', $orderBy)
            ->get();
    }
}
