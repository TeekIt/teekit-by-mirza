<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Van;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Categories;
use App\Models\VanOperativeProductUsage;
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
        'created_at',
        'updated_at',
    ];

    /**
     * Attributes to append to JSON
     *
     * @var array
     */
    protected $appends = ['status'];

    /**
     * Attribute type casting
     *
     * @var array
     */
    protected $casts = [
        'price'         => 'float',
        'weight'        => 'float',
        'height'        => 'float',
        'width'         => 'float',
        'length'        => 'float',
        'quantity'      => 'integer',
        'featured'      => 'boolean',
        'bike'          => 'boolean',
        'car'           => 'boolean',
        'colors'        => 'array',
        'min_threshold' => 'integer',
    ];

    /**
     * Relationship: Product belongs to a Van
     */
    public function van()
    {
        return $this->belongsTo(Van::class);
    }

    /**
     * Relationship: Product belongs to a Seller (User)
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Relationship: Product belongs to a Category
     */
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    
    /**
     * Fetch a single product by ID with relations
     *
     * @param int $id
     * @return VanProduct|null
     */
   public static function getById(int $productId, int $vanId)
    {
    return self::where('id', $productId)
        ->where('van_id', $vanId)
        ->first();
    }
    /**
     * Search products for a van by query string
     *
     * @param int $vanId
     * @param string $query
     * @return Collection
     */

    public static function searchByVan(int $vanId, string $query)
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
   

    /**
     * Fetch single product by ID
     *
     * @param int $productId
     * @return VanProduct|null
     */
    public static function getByProductId(int $productId)
    {
        return self::where('id', $productId)->first();
    }

    /**
     * Search products by product_name or SKU
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchProducts(string $query)
    {
        return self::with(['category', 'seller'])
                   ->where('product_name', 'like', "%{$query}%")
                   ->orWhere('sku', 'like', "%{$query}%")
                   ->latest()
                   ->get();
    }

    /**
     * Scope for category filter
     */
    public function scopeCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Scope for status filter
     */
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

    /**
     * Accessor for dynamic status attribute
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        if ($this->quantity == 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= $this->min_threshold) {
            return 'critical';
        } else {
            return 'in_stock';
        }
    }

    /**
 * Use quantity of this product (stock update)
 */
    public function useQuantity(int $quantityUsed): bool
    {
        if ($this->quantity < $quantityUsed) {
            return false;
        }

        $this->decrement('quantity', $quantityUsed);
        return true;
    }
    /**
     * Fetch recent products for a specific van
     *
     * @param int $vanId
     * @param array $columns
     * @return Collection
     */
    public static function getRecentByVan(int $vanId, array $columns = ['*']): Collection
    {
        return self::where('van_id', $vanId)
            ->latest('updated_at')
            ->limit(10)
            ->get($columns);
    }

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

    // Category Filter
    if (!empty($filters['category_id'])) {
        $query->where('category_id', (int) $filters['category_id']);
    }
    if (!empty($filters['id'])) {
        $query->where('id', (int) $filters['id']);
    }

    // Status Filter (Fixed - whereColumn ki jagah normal where)
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
    {
    DB::transaction(function () use ($order, $vanId) {
        foreach ($order->order_items as $item) {
    $product = $item->product;

    if (! $product) {
        continue;
    }
            VanProduct::create([
                'seller_id' => $order->seller_id,
                'van_id' => $vanId,
                'category_id' => $item->product->category_id ?? null,
                'product_name' => $item->product->name ?? 'N/A',
                'sku' => $item->product->sku ?? 'N/A',
                'price' => $item->price ?? 0,
                'quantity' => $item->quantity ?? 1,
                'feature_img' => $item->product->feature_img ?? null,
                'brand' => $item->product->brand ?? null,
                'weight' => $item->product->weight ?? null,
                'size' => $item->product->size ?? null,
                'status' => 'active',
                'contact' => $order->phone_number ?? '',
                'colors' => isset($item->product->colors)
                 ? json_encode($item->product->colors)
                    : null,
                'bike' => null,
                'car' => null,
                'height' => $item->product->height ?? null,
                'width' => $item->product->width ?? null,
                'length' => $item->product->length ?? null,
                'job_reference' => $order->id,
                'discount_percentage' => 0,
                'featured' => 0,
            ]);
             }

        $order->order_status = 'complete';
        $order->save();
    });
}
    
}