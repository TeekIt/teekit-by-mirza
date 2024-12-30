<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\OrderItems;
use App\Orders;
use App\Products;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
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
            'updated_at' => now()
        ]);
    }

    public static function insertInfo(
        int $customer_id,
        int $seller_id,
        int $product_id,
        float $product_price,
        int $product_qty,
        float $initial_total,
        ?float $customer_lat = null,
        ?float $customer_lon = null,
        string $receiver_name,
        string $phone_number,
        string $address,
        string $house_no = null,
        string $flat = null,
        float $driver_charges = 0.0,
        ?float $delivery_charges = null,
        ?float $service_charges = null,
        string $device = null,
        string $type,
        ?string $description = null,
        string $payment_status = "hidden",
        ?int $offloading = null,
        ?float $offloading_charges = null
    ): OrdersFromOtherSeller {
        $model = new OrdersFromOtherSeller();
        $model->customer_id = $customer_id;
        $model->seller_id = $seller_id;
        $model->product_id = $product_id;
        $model->product_price = $product_price;
        $model->product_qty = $product_qty;
        $model->initial_total = $initial_total;
        if ($type == 'delivery') {
            $model->customer_lat = $customer_lat;
            $model->customer_lon = $customer_lon;
            $model->customer_name = $receiver_name;
            $model->phone_number = $phone_number;
            $model->address = $address;
            $model->house_no = $house_no;
            $model->flat = $flat;
            $model->driver_charges = $driver_charges;
            $model->delivery_charges = $delivery_charges;
            $model->service_charges = $service_charges;
        }
        $model->device = $device;
        $model->type = $type;
        $model->description = $description;
        $model->payment_status = $payment_status;
        $model->offloading = $offloading;
        $model->offloading_charges = $offloading_charges;
        $model->save();

        return $model;
    }

    public static function getById(array $columns, int $id): object
    {
        return self::select($columns)
            ->with(['product.category', 'seller', 'customer'])
            ->where('id', '=', $id)
            ->first();
    }

    public static function getForView(array $columns, int $seller_id, string $order_by): object
    {
        return self::select($columns)->with(['product.category'])
            ->where('seller_id', '=', $seller_id)
            ->orderBy('created_at', $order_by)
            ->get();
    }
}
