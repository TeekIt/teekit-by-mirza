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

    /**
     * Attributes to append to JSON
     *
     * @var array
     */
    protected $appends = ['status'];

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

    public static function searchByVan(int $vanId, string $query): Collection
    {
        return self::query()
            ->where('van_id', $vanId)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('product_name', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%")
                        ->orWhere('brand', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->get();
    }

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

    public static function addBulk(VanInventoryOrder $vanInventoryOrder): bool
    {
        $now = now();
        $rows = $vanInventoryOrder->orderItems->map(function ($item) use ($vanInventoryOrder, $now) {
            return [
                'seller_id' => $item->seller_id,
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
                'min_threshold' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })
            ->values()
            ->all();

        return VanProduct::insert($rows);
    }

    public static function getRecentByVan(int $vanId, array $columns = ['*']): Collection
    {
        return self::where('van_id', $vanId)
            ->latest('updated_at')
            ->limit(10)
            ->get($columns);
    }

<<<<<<< HEAD
        /**
        * Fetch products for a van with optional filters
        *
        * @param array $filters
        * @param int $vanId
        * @return Collection
        */
    public static function getFilteredProducts(array $filters, int $vanId)
    {
        $query = self::query()->where('van_id', $vanId);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['status'])) {
            $status = strtolower(trim($filters['status']));

            if ($status === 'in_stock') {
                $query->whereColumn('quantity', '>', 'min_threshold');
            } 
            elseif ($status === 'critical') {
                $query->whereColumn('quantity', '<=', 'min_threshold')
                    ->where('quantity', '>', 0);
            } 
            elseif (in_array($status, ['out_of_stock', 'outofstock'])) {
                $query->where('quantity', '=', 0);
            }
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

  public static function add(Orders $order, int $vanId): void
=======
    public static function getFilteredProducts(array $filters, int $vanId)
>>>>>>> dec7bf714545a743664f3de46818a8630c85d910
    {
        $query = self::query()->where('van_id', $vanId);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', '=',(int) $filters['category_id']);
        }
        
        if (!empty($filters['id'])) {
            $query->where('id', '=', (int) $filters['id']);
        }

        if (!empty($filters['status'])) {
            $status = strtolower(trim($filters['status']));

            if ($status === VanProductStatusEnum::IN_STOCK->value) {
                $query->whereColumn('quantity', '>', 0);
            } elseif ($status === VanProductStatusEnum::LOW_STOCK->value) {
                $query->whereColumn('quantity', '<', 'min_threshold')
                    ->where('quantity', '>', 0);
            } elseif ($status === VanProductStatusEnum::OUT_OF_STOCK->value) {
                $query->where('quantity', '=', 0);
            }
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    public static function getById(int $id, array $columns = ['*']): VanProduct
    {
        return self::select($columns)->where('id', '=', $id)->firstOrFail();
    }

    public static function getAll(
        OrderByEnum $orderBy,
        string $search = '',
        ?int $vanId = null,
        int $perPage = 10,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->when($search, function ($query) use ($search) {
                $search = trim(mb_strtolower($search));
                $query->where(function ($query) use ($search) {
                    $query->where('product_name', 'like', '%' . $search . '%')
                        ->orWhere('price', 'like', '%' . $search . '%')
                        ->orWhere('min_threshold', 'like', '%' . $search . '%');
                });
            })
            ->when($vanId, function ($query) use ($vanId) {
                $query->where('van_id', '=', $vanId);
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate($perPage);
    }
}
