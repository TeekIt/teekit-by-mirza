<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * Relations
     */
    public function vanInventoryOrderItems(): HasMany
    {
        return $this->hasMany(VanInventoryOrderItem::class);
    }

    /**
     * Helpers
     */
    public static function add(
        int $companyId,
        int $vanId,
        float $orderTotal,
        OrderTypeEnum $type,
        string $vanLocation,
    ): self {
        return self::create([
            'company_id' => $companyId,
            'van_id' => $vanId,
            'order_total' => $orderTotal,
            'status' => OrderStatusEnum::PENDING->value,
            'type' => $type->value,
            'van_location' => $vanLocation,
        ]);
    }
}
