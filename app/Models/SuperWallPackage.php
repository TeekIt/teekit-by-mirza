<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SuperWallPackage extends Model
{
    /** @use HasFactory<\Database\Factories\SuperWallPackageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'super_fast_deliveries',
        'unlimited_super_fast_deliveries_on_orders_above',
        'currency',
        'min_delivery_time_in_minutes',
        'guarantee_delivery_time_in_minutes',
        'cashback_percentage',
        'van_deliveries_discount_percentage',
        'priority_support',
        'users_included',
        'free_same_day_delivery',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Helpers
     */
    public static function add(
        string $name,
        string $superFastDeliveries,
        ?int $unlimitedSuperFastDeliveriesOnOrdersAbove = null,
        ?string $currency = null,
        ?int $minDeliveryTimeInMinutes = null,
        ?string $guaranteeDeliveryTimeInMinutes = null,
        ?string $cashbackPercentage = null,
        ?string $vanDeliveriesDiscountPercentage = null,
        ?string $prioritySupport = null,
        ?string $usersIncluded = null,
        ?string $freeSameDayDelivery = null,
    ): self {
        return self::create([
            'name' => $name,
            'super_fast_deliveries' => $superFastDeliveries,
            'unlimited_super_fast_deliveries_on_orders_above' => $unlimitedSuperFastDeliveriesOnOrdersAbove,
            'currency' => $currency,
            'min_delivery_time_in_minutes' => $minDeliveryTimeInMinutes,
            'guarantee_delivery_time_in_minutes' => $guaranteeDeliveryTimeInMinutes,
            'cashback_percentage' => $cashbackPercentage,
            'van_deliveries_discount_percentage' => $vanDeliveriesDiscountPercentage,
            'priority_support' => $prioritySupport,
            'users_included' => $usersIncluded,
            'free_same_day_delivery' => $freeSameDayDelivery,
        ]);
    }

    public static function getForView(
        OrderByEnum $orderBy,
        array $columns = ['*']
    ): Collection {
        return self::select($columns)
            ->orderBy('created_at', $orderBy->value)
            ->get();
    }

    public static function updateInfo(
        int $id,
        ?string $name = null,
        ?string $superFastDeliveries = null,
        ?int $unlimitedSuperFastDeliveriesOnOrdersAbove = null,
        ?string $currency = null,
        ?int $minDeliveryTimeInMinutes = null,
        ?string $guaranteeDeliveryTimeInMinutes = null,
        ?string $cashbackPercentage = null,
        ?string $vanDeliveriesDiscountPercentage = null,
        ?string $prioritySupport = null,
        ?string $usersIncluded = null,
        ?string $freeSameDayDelivery = null,
    ): bool {
        $superWallPackage = self::findOrFail($id);

        if (! is_null($name)) {
            $superWallPackage->name = $name;
        }
        if (! is_null($superFastDeliveries)) {
            $superWallPackage->super_fast_deliveries = $superFastDeliveries;
        }
        if (! is_null($unlimitedSuperFastDeliveriesOnOrdersAbove)) {
            $superWallPackage->unlimited_super_fast_deliveries_on_orders_above = $unlimitedSuperFastDeliveriesOnOrdersAbove;
        }
        if (! is_null($currency)) {
            $superWallPackage->currency = $currency;
        }
        if (! is_null($minDeliveryTimeInMinutes)) {
            $superWallPackage->min_delivery_time_in_minutes = $minDeliveryTimeInMinutes;
        }
        if (! is_null($guaranteeDeliveryTimeInMinutes)) {
            $superWallPackage->guarantee_delivery_time_in_minutes = $guaranteeDeliveryTimeInMinutes;
        }
        if (! is_null($cashbackPercentage)) {
            $superWallPackage->cashback_percentage = $cashbackPercentage;
        }
        if (! is_null($vanDeliveriesDiscountPercentage)) {
            $superWallPackage->van_deliveries_discount_percentage = $vanDeliveriesDiscountPercentage;
        }
        if (! is_null($prioritySupport)) {
            $superWallPackage->priority_support = $prioritySupport;
        }
        if (! is_null($usersIncluded)) {
            $superWallPackage->users_included = $usersIncluded;
        }
        if (! is_null($freeSameDayDelivery)) {
            $superWallPackage->free_same_day_delivery = $freeSameDayDelivery;
        }

        return $superWallPackage->save();
    }

    public static function deletePermanently(int $id): bool
    {
        return self::where('id', '=', $id)->forceDelete();
    }
}
