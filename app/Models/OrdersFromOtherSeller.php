<?php

namespace App\Models;

use App\OrderItems;
use App\Products;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdersFromOtherSeller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['*'];
    /**
     * Relations
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Products::class,
            OrderItems::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }

    /**
     * Helpers
     */
    public static function insertOrderFromOtherSeller(
        int $customer_id,
        int $seller_id,
        float $order_total,
        int $total_items,
        ?float $customer_lat = null, // Optional parameter with default null
        ?float $customer_lon = null, // Optional parameter with default null
        string $receiver_name,
        string $phone_number,
        string $address,
        string $house_no = null,      // Optional parameter with default null
        string $flat = null,          // Optional parameter with default null
        float $driver_charges = 0.0, // Optional parameter with default value
        ?float $delivery_charges = null, // Optional parameter with default null
        ?float $service_charges = null, // Optional parameter with default null
        string $device = null,       // Optional parameter with default null
        string $type,
        ?string $description = null,
        string $payment_status = "hidden", // Optional parameter with default value
        ?int $offloading = null,      // Optional parameter with default null
        ?float $offloading_charges = null // Optional parameter with default null
    ): OrdersFromOtherSeller {
        $model = new OrdersFromOtherSeller();
        $model->customer_id = $customer_id;
        $model->seller_id = $seller_id;
        $model->order_total = $order_total;
        $model->total_items = $total_items;
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

    public static function getOrdersFromOtherSellersForView(array $columns, int $seller_id, string $order_by): object
    {
        return self::select($columns)->with(['products.category'])
            ->where('seller_id', '=', $seller_id)
            ->orderBy('created_at', $order_by)
            ->get();
    }
}
