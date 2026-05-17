<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use App\Enums\VanProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Van;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Categories;
use App\Models\VanOperativeProductUsage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VanProduct extends Model
{
    use HasFactory;

    /**
     * Fields protected from mass assignment
     *
     * @var array
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
    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    /**
     * Laravel Built-In Helpers
     */
    public function getStatusAttribute()
    {
        if ($this->quantity == 0) {
            return VanProductStatusEnum::OUT_OF_STOCK->value;
        } elseif ($this->quantity < $this->min_threshold) {
            return VanProductStatusEnum::LOW_STOCK->value;
        } else {
            return VanProductStatusEnum::IN_STOCK->value;
        }
    }

    /**
     * Scopes
     */
    public function scopeCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }

        $status = strtolower($status);

        if ($status === 'out_of_stock') {
            $query->where('quantity', 0);
        } elseif ($status === 'critical') {
            $query->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'min_threshold');
        } elseif ($status === 'active') {
            $query->whereColumn('quantity', '>', 'min_threshold');
        }

        return $query;
    }

    // public static function searchByVan(int $vanId, string $query): Collection
    // {
    //     return self::query()
    //         ->where('van_id', $vanId)
    //         ->when($query, function ($q) use ($query) {
    //             $q->where(function ($sub) use ($query) {
    //                 $sub->where('product_name', 'like', "%{$query}%")
    //                     ->orWhere('sku', 'like', "%{$query}%")
    //                     ->orWhere('brand', 'like', "%{$query}%");
    //             });
    //         })
    //         ->latest()
    //         ->get();
    // }

    /**
     * Helpers
     */
    public function searchProducts(string $query)
    {
        return self::with(['category', 'seller'])
            ->where('product_name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->latest()
            ->get();
    }

    public function useQuantity(int $quantityUsed): bool
    {
        if ($this->quantity < $quantityUsed) {
            return false;
        }

        $this->decrement('quantity', $quantityUsed);

        return true;
    }

    public static function add(array $data): VanProduct
    {
        return self::create($data);
    }

    public static function addBulkFromProductsTable(Collection $products, Collection $quantitiesMap, int $vanId): bool
    {
        $now = now();
        $vanProducts = $products->map(fn($product) => [
            'seller_id' => $product->seller_id,
            'category_id' => $product->category_id,
            'van_id' => $vanId,
            'product_name' => $product->product_name,
            'sku' => $product->sku,
            'price' => $product->price,
            'quantity' => $quantitiesMap[$product->id]['qty'],
            'featured' => 0,
            'discount_percentage' => $product->discount_percentage,
            'weight' => $product->weight,
            'brand' => $product->brand,
            'size' => $product->size,
            'contact' => $product->contact ?? '',
            'colors' => is_array($product->colors) ? json_encode($product->colors) : $product->colors,
            'bike' => $product->bike,
            'car' => $product->car,
            'van' => $product->van,
            'feature_img' => $product->feature_img,
            'height' => $product->height,
            'width' => $product->width,
            'length' => $product->length,
            'created_at' => $now,
        ])->toArray();

        return self::insert($vanProducts);
    }

    public static function addBulkFromVanInventoryOrder(VanInventoryOrder $vanInventoryOrder): bool
    {
        $now = now();
        $rows = $vanInventoryOrder->orderItems->map(function ($item) use ($vanInventoryOrder, $now) {
            return [
                'seller_id' => $vanInventoryOrder->seller_id,
                'category_id' => $item->product->category_id,
                'van_id' => $vanInventoryOrder->van_id,
                'product_name' => $item->product->product_name,
                'sku' => $item->product->sku,
                'price' => $item->product_price,
                'featured' => 0,
                'discount_percentage' => '0',
                'weight' => $item->product->weight,
                'brand' => $item->product->brand,
                'size' => $item->product->size,
                'contact' => '',
                'colors' => isset($item->product->colors)
                    ? json_encode($item->product->colors)
                    : null,
                'bike' => null,
                'car' => null,
                'van' => null,
                'feature_img' => $item->product->feature_img,
                'height' => $item->product->height,
                'width' => $item->product->width,
                'length' => $item->product->length,
                'job_reference' => null,
                'quantity' => $item->product_qty,
                'created_at' => $now,
            ];
        })
            ->values()
            ->all();

        return self::insert($rows);
    }

    public static function getRecentByVan(int $vanId, array $columns = ['*']): Collection
    {
        return self::where('van_id', $vanId)
            ->latest('updated_at')
            ->limit(10)
            ->get($columns);
    }

    public static function getById(int $id, array $columns = ['*']): VanProduct
    {
        return self::select($columns)->with(['category:id,category_name'])->where('id', '=', $id)->firstOrFail();
    }

    public static function getAll(
        OrderByEnum $orderBy,
        ?string $search = null,
        ?int $vanId = null,
        ?int $categoryId = null,
        ?VanProductStatusEnum $status = null,
        int $perPage = 10,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->when($search, function ($query) use ($search) {
                $search = trim(mb_strtolower($search));
                $query->where(function ($query) use ($search) {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%")
                        ->orWhere('min_threshold', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($vanId, function ($query) use ($vanId) {
                $query->where('van_id', '=', $vanId);
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', '=', $categoryId);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', '=', $status->value);
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate($perPage);
    }
}
