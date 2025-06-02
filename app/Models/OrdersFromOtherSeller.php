<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdersFromOtherSeller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['*'];
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

    public static function isViewed(int $id): object
    {
        $order = self::findOrFail($id);
        $order->is_viewed = 1;
        $order->save();
        return $order;
    }

    public static function orderAccepted(int $id): int
    {
        return self::where('id', '=', $id)->update(['accepted' => 1]);
    }

    public static function incrementTimesRejected(int $id): int
    {
        return self::where('id', '=', $id)->increment('times_rejected');
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
        ?float $customerLat = null,
        ?float $customerLon = null,
        string $receiverName,
        string $countryCode,
        string $phoneNumber,
        string $address,
        ?string $houseNo = null,
        ?string $flat = null,
        string $country,
        string $state,
        string $city,
        string $postcode,
        string $paymentIntentId,
        float $driverCharges = 0.0,
        ?float $deliveryCharges = null,
        ?float $serviceCharges = null,
        ?string $device = null,
        string $type,
        ?string $description = null,
        string $paymentStatus = "hidden",
        ?int $offloading = null,
        ?float $offloadingCharges = null,
        string $movedAt,
        string $createdAt,
    ): OrdersFromOtherSeller {
        $model = new self();
        $model->created_by_type = $createdByType;
        $model->created_by_id = $createdById;
        $model->seller_id = $sellerId;
        $model->parent_order_id = $parentOrderId; // Assuming this is a new order, set to 0 or adjust as needed
        $model->product_belongs_to_type = $productBelongsToType;
        $model->product_belongs_to_id = $productBelongsToId;
        $model->product_price = $productPrice;
        $model->product_qty = $productQty;
        $model->initial_total = $initialTotal;
        // if ($type == OrderTypeEnum::DELIVERY->value) {
        //     $model->customer_lat = $customerLat;
        //     $model->customer_lon = $customerLon;
        //     $model->customer_name = $receiverName;
        //     $model->phone_number = $phoneNumber;
        //     $model->address = $address;
        //     $model->house_no = $houseNo;
        //     $model->flat = $flat;
        //     $model->driver_charges = $driverCharges;
        //     $model->delivery_charges = $deliveryCharges;
        //     $model->service_charges = $serviceCharges;
        // }
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

    public static function getById(array $columns = ['*'], int $id): object
    {
        return self::select($columns)
            ->with(['product', 'buyer', 'seller'])
            ->where('id', '=', $id)
            ->first();
    }

    public static function getForView(array $columns = ['*'], int $sellerId, string $orderBy): Collection
    {
        return self::select($columns)->with('product')
            ->where('seller_id', '=', $sellerId)
            ->orderBy('created_at', $orderBy)
            ->get();
    }
}
