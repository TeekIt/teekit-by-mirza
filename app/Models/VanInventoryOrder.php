<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Relations
     */
    public function orderItems(): HasMany
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
            'order_status' => OrderStatusEnum::PENDING->value,
            'type' => $type->value,
            'van_location' => $vanLocation,
        ]);
    }

    public static function getAll(
        OrderByEnum $orderBy,
        ?int $vanInventoryOrderId = null,
        ?int $companyId = null,
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
            ->orderBy('created_at', $orderBy->value)
            ->paginate(10);
    }
}
