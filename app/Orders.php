<?php

namespace App;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TransportVehicleEnum;
use App\Enums\UserMorphTypeEnum;
use App\Models\ProductsByBuyer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Orders extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['*'];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    /**
     * Relations
     */
    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Products::class,
            OrderItems::class,
            'order_id',
            'id',
            'id',
            'product_belongs_to_id'
        );
    }
    /**
     * Helpers
     */
    public static function remove(int $id): int
    {
        return self::where('id', '=', $id)->forceDelete();
    }

    public static function updateInfo(
        int $id,
        ?float $initialTotal = null,
        ?float $currentTotal = null,
        ?OrderStatusEnum $orderStatus = null
    ): bool {
        $order = self::findOrFail($id);
        if (!is_null($initialTotal)) $order->initial_total = $initialTotal;
        if (!is_null($currentTotal)) $order->current_total = $currentTotal;
        if (!is_null($orderStatus)) $order->order_status = $orderStatus;

        return $order->save();
    }

    // public static function moveToAnotherSeller(int $id, int $sellerId): int
    // {
    //     return self::where('id', '=', $id)->update([
    //         'seller_id' => $sellerId,
    //         'moved_at' => now(),
    //     ]);
    // }

    public static function add(
        string $createdByType,
        int $createdById,
        int $sellerId,
        float $initialTotal,
        int $totalItems,
        float $driverCharges,
        Request $request
    ): Orders {
        $order = new self();
        $order->created_by_type = $createdByType;
        $order->created_by_id = $createdById;
        $order->seller_id = $sellerId;
        $order->initial_total = $initialTotal;
        /* When we create a new order current_total == initial_total */
        $order->current_total = $initialTotal;
        $order->total_items = $totalItems;
        // if ($request->type == OrderTypeEnum::DELIVERY->value) {
        //     $order->customer_lat = $request->lat;
        //     $order->customer_lon = $request->lon;
        //     $order->customer_name = $request->fName . " " .  $request->lName;
        //     $order->phone_number = $request->phone;
        //     $order->address = $request->fullAddress;
        //     $order->house_no = $request->houseNo;
        //     $order->flat = $request->flat;
        //     $order->driver_charges = $driverCharges;
        //     $order->delivery_charges = $request->deliveryCharges;
        //     $order->service_charges = $request->serviceCharges;
        // }

        /* If order type == self-pickup even then we need this information */
        $order->customer_lat = $request->lat;
        $order->customer_lon = $request->lon;
        $order->customer_name = $request->fName . " " .  $request->lName;
        $order->country_code = $request->countryCode;
        $order->phone_number = $request->phone;
        $order->address = $request->fullAddress;
        $order->house_no = $request->houseNo;
        $order->country = $request->country;
        $order->state = $request->state;
        $order->city = $request->city;
        $order->postcode = $request->postcode;
        $order->driver_charges = $driverCharges;
        $order->delivery_charges = $request->deliveryCharges;
        $order->service_charges = $request->serviceCharges;

        $order->type = $request->type;
        $order->description = $request->description;
        $order->payment_status = $request->paymentStatus ?? "hidden";
        $order->payment_intent_id = $request->paymentIntentId;
        $order->device = $request->device ?? NULL;
        $order->offloading = $request->offloading ?? NULL;
        $order->offloading_charges = $request->offloadingCharges ?? NULL;
        $order->save();

        return $order;
    }

    public static function subFromOrderTotal(int $orderId, float $prodTotalPrice): bool
    {
        $order = self::find($orderId);
        $order->initial_total -= $prodTotalPrice;

        return $order->save();
    }

    public static function replaceWithAlternativePrice(int $orderId, float $currentProdPrice, float $alternativeProdPrice): bool
    {
        $order = self::find($orderId);
        $order->current_total = ($order->current_total - $currentProdPrice) + $alternativeProdPrice;

        return $order->save();
    }


    public static function fetchTransportType(?int $order_id = null): string
    {
        $transposrt_type = [];
        $product_ids = OrderItems::where('order_id', '=', $order_id)->pluck('product_id');
        $products = Products::whereIn('id', $product_ids)->get();
        /**
         * First populate the array $transposrt_type
         */
        foreach ($products as $single_product) {
            if ($single_product->van)
                array_push($transposrt_type, TransportVehicleEnum::VAN->value);
            elseif ($single_product->car)
                array_push($transposrt_type, TransportVehicleEnum::CAR->value);
            elseif ($single_product->bike)
                array_push($transposrt_type, TransportVehicleEnum::BIKE->value);
        }
        /**
         * Now if any product contains "van" then the function should return "van"
         * If any product contains "car" then return "car"
         * Otherwise "bike"
         */
        if (in_array(TransportVehicleEnum::VAN->value, $transposrt_type))
            return TransportVehicleEnum::VAN->value;
        elseif (in_array(TransportVehicleEnum::CAR->value, $transposrt_type))
            return TransportVehicleEnum::CAR->value;
        else
            return TransportVehicleEnum::BIKE->value;
    }

    public static function checkIfOrderExists(int $id): bool
    {
        return self::where('id', '=', $id)->exists();
    }

    public static function checkTotalOrders(int $customerId): int
    {
        return self::where('customer_id', '=', $customerId)->count();
    }

    public static function updateOrderStatus(int $id, OrderStatusEnum $status): int
    {
        return self::where('id', '=', $id)->update([
            'order_status' => $status
        ]);
    }

    public static function isViewed(int $orderId): ?Orders
    {
        $order = self::findOrFail($orderId);
        $order->is_viewed = 1;
        $order->save();

        return $order;
    }

    public static function getTotalSalesBySellerId(int $seller_id): float
    {
        return self::where('payment_status', '=', 'paid')->where('seller_id', '=', $seller_id)->sum('initial_total');
    }

    public static function getTotalOrdersBySellerId(int $seller_id): Collection
    {
        return self::where('payment_status', '!=', 'hidden')->where('seller_id', '=', $seller_id)->get();
    }

    public static function getOrdersByStatusWhereSellerId(int $seller_id, string $status): Collection
    {
        return self::where('order_status', '=', $status)->where('seller_id', '=', $seller_id)->get();
    }

    public static function getLoggedinBuyerOrders(
        ?OrderStatusEnum $orderStatus,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->with(['order_items.product.store'])
            ->when($orderStatus, function ($query) use ($orderStatus) {
                return $query->where('order_status', '=', $orderStatus);
            })
            ->where('created_by_type', UserMorphTypeEnum::USER)
            ->where('created_by_id', '=', auth()->id())
            ->paginate(20);
    }

    public static function getOrdersOfUniqueProductsForView(
        int $sellerId,
        string $orderBy,
        ?int $orderId = null,
        array $columns = ['*'],
    ): LengthAwarePaginator {
        /* First we will update the "is_viewed" column if the order is searched by ID */
        if ($orderId) static::isViewed($orderId);
        /* Now we will fetch the required data */
        return self::select($columns)
            ->with(['order_items.product'])
            ->when($orderId, function ($query) use ($orderId) {
                return $query->where('id', '=', $orderId);
            })
            ->whereHas('order_items', function ($orderItemsQuery) {
                $orderItemsQuery->where('product_belongs_to_type', (new ProductsByBuyer())->getMorphClass());
            })
            ->where('seller_id', '=', $sellerId)
            ->orderBy('created_at', $orderBy)
            ->paginate(10);
    }

    public static function getOrdersForSuperAdminView(string $orderBy, int|null $orderId = null): LengthAwarePaginator
    {
        /* First we will update the "is_viewed" column if the order is searched by ID */
        if ($orderId) static::isViewed($orderId);
        /* Now we will fetch the required data */
        $orders = self::with(['order_items.product'])
            ->when($orderId, function ($query) use ($orderId) {
                return $query->where('id', '=', $orderId);
            })
            ->orderBy('created_at', $orderBy)
            ->paginate(10);
        /* 
        * Load 'category' for products where 'product_belongs_to_type' is 'Product'
        * Means if the product has been created by a 'seller' not a 'buyer' 
        * Because only seller products have 'category'
        */
        $orders->each(function ($order) {
            $order->order_items->each(function ($orderItem) {
                if ($orderItem->product_belongs_to_type == (new Products())->getMorphClass()) {
                    $orderItem->product->load('category');
                }
            });
        });

        return $orders;
    }

    public static function getOrdersForView(int $sellerId, string $orderBy, int|null $orderId = null): LengthAwarePaginator
    {
        /* First we will update the "is_viewed" column if the order is searched by ID */
        if ($orderId) static::isViewed($orderId);
        /* Now we will fetch the required data */
        $orders = self::with(['order_items.product'])
            ->when($orderId, function ($query) use ($orderId) {
                return $query->where('id', '=', $orderId);
            })
            ->where('seller_id', '=', $sellerId)
            ->orderBy('created_at', $orderBy)
            ->paginate(10);
        /* 
        * Load 'category' for products where 'product_belongs_to_type' is 'Product'
        * Means if the product has been created by a 'seller' not a 'buyer' 
        * Because only seller products have 'category'
        */
        $orders->each(function ($order) {
            $order->order_items->each(function ($orderItem) {
                if ($orderItem->product_belongs_to_type == (new Products())->getMorphClass()) {
                    $orderItem->product->load('category');
                }
            });
        });

        return $orders;
    }

    public static function getRecentOrderByBuyerId(
        int $buyerId,
        ?int $productsLimit = null,
        ?int $sellerId = null
    ): ?Orders {
        return self::with([
            'products' => function ($query) use ($productsLimit) {
                if ($productsLimit !== null) $query->take($productsLimit);
            }
        ])
            ->when($sellerId, fn($query) => $query->where('seller_id', '=', $sellerId))
            ->where('created_by_id', '=', $buyerId)
            ->latest()
            ->first();
    }

    public static function getByIds(array $ids, array $columns = ['*']): Collection
    {
        return self::select($columns)
            ->with(['order_items.product', 'buyer', 'seller'])
            ->whereIn('id', $ids)
            ->get();
    }

    public static function getById(int $id, array $columns = ['*']): ?Orders
    {
        return self::select($columns)
            ->with(['order_items.product', 'buyer', 'seller'])
            ->where('id', '=', $id)
            ->first();
    }
}
