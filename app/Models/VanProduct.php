<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Van;
use App\Models\User;
use App\Models\Categories;

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
     * Get products by Van ID
     *
     * @param int $vanId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByVanId(int $vanId)
    {
        return self::where('van_id', $vanId)
                   ->with(['seller', 'category'])
                   ->latest()
                   ->get();
    }

    /**
     * Get active products by Van ID
     *
     * @param int $vanId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveByVanId(int $vanId)
    {
        return self::where('van_id', $vanId)
                   ->where('status', 'active')
                   ->with(['seller', 'category'])
                   ->latest()
                   ->get();
    }

    /**
     * Fetch a single product by ID with relations
     *
     * @param int $id
     * @return VanProduct|null
     */
    public static function getById(int $id)
    {
        return self::with(['seller', 'van', 'category'])
                   ->find($id);
    }

    /**
     * List products with optional filters
     *
     * Filters: category_id, status, critical/out_of_stock handling
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listProducts(array $filters = [])
    {
        $query = self::with(['category', 'seller']);

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Status filter with dynamic threshold handling
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            if ($status === 'critical') {
                $query->whereColumn('quantity', '<=', 'min_threshold')
                      ->where('quantity', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', 0);
            } else {
                $query->where('status', $status);
            }
        }

        $products = $query->latest()->get();

        // Attach dynamic status for every product
        $products->transform(fn($product) => $product->append('dynamic_status'));

        return $products;
    }

    /**
     * Fetch single product by ID with relations
     *
     * @param int $id
     * @return VanProduct|null
     */
    public static function getByProductId(int $id)
    {
        return self::with(['category', 'seller'])->find($id);
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
     * Accessor: Get dynamic status based on quantity and min_threshold
     *
     * @return string
     */
    public function getDynamicStatusAttribute(): string
    {
        if ($this->quantity == 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= $this->min_threshold) {
            return 'critical';
        }

        return $this->status; // active or inactive
    }
}