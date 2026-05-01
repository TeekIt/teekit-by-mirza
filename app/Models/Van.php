<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\VanProductStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\VanOperativeProductUsage;
use App\Models\VanProduct;
use App\Models\VanInventoryOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Van extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\VanFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Laravel Built-In Helpers
     */
    protected function userName(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value),
        );
    }

    protected function numberPlate(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtoupper($value),
        );
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relations
     */
    public function vanProducts(): HasMany
    {
        return $this->hasMany(VanProduct::class, 'van_id');
    }

    public function activeVanProducts(): HasMany
    {
        return $this->hasMany(VanProduct::class, 'van_id')->where('status', 'active');
    }

    /**
     * Helpers
     */
    public function getValidationRules(?int $vanId = null): array
    {
        return [
            'userName' => ($vanId ? "unique:vans,user_name,{$vanId}" : 'unique:vans,user_name') . '|required|string|max:20|regex:/^\S+$/',
            'operative' => 'required|string|max:20',
            'numberPlate' => ($vanId ? "unique:vans,number_plate,{$vanId}" : 'unique:vans,number_plate') . '|required|string|max:20',
            'payload' => 'required|integer|min:1',
            'width' => 'required|numeric|min:1.0',
            'height' => 'required|numeric|min:1.0',
            'length' => 'required|numeric|min:1.0',
            'password' => ($vanId ? 'nullable' : 'required') . '|string|min:6',
        ];
    }

    public static function add(
        string $userName,
        string $operative,
        string $numberPlate,
        int $payload,
        float $width,
        float $height,
        float $length,
        string $password
    ): Van {
        return self::create([
            'company_id' => User::getAuthUser()->id,
            'user_name' => $userName,
            'operative' => $operative,
            'number_plate' => $numberPlate,
            'payload' => $payload,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'password' => bcrypt($password),
        ]);
    }

    public static function updateInfo(
        int $id,
        ?string $userName = null,
        ?string $operative = null,
        ?string $numberPlate = null,
        ?int $payload = null,
        ?float $width = null,
        ?float $height = null,
        ?float $length = null,
        ?string $password = null
    ): bool {
        $van = self::findOrFail($id);

        if (! is_null($userName)) {
            $van->user_name = $userName;
        }

        if (! is_null($operative)) {
            $van->operative = $operative;
        }

        if (! is_null($numberPlate)) {
            $van->number_plate = $numberPlate;
        }

        if (! is_null($payload)) {
            $van->payload = $payload;
        }

        if (! is_null($width)) {
            $van->width = $width;
        }

        if (! is_null($height)) {
            $van->height = $height;
        }

        if (! is_null($length)) {
            $van->length = $length;
        }

        if (! is_null($password)) {
            $van->password = bcrypt($password);
        }

        return $van->save();
    }

    public static function getByUserName(string $userName, array $columns = ['*']): Van
    {
        return self::select($columns)->where('user_name', '=', $userName)->firstOrFail();
    }

    public static function getByCompanyId(int $companyId, array $columns = ['*']): Collection
    {
        return self::select($columns)->where('company_id', '=', $companyId)->get();
    }

    public static function getById(int $id, array $columns = ['*']): Van
    {
        return self::select($columns)->where('id', '=', $id)->firstOrFail();
    }

    public static function getAll(
        OrderByEnum $orderBy,
        string $search = '',
        ?int $companyId = null,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->when($search, function ($query) use ($search) {
                $search = trim(mb_strtolower($search));
                $query->where(function ($query) use ($search) {
                    $query->where('user_name', 'like', '%' . $search . '%')
                        ->orWhere('operative', 'like', '%' . $search . '%')
                        ->orWhere('number_plate', 'like', '%' . $search . '%');
                });
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', '=', $companyId);
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate(10);
    }
    /**
     * Get total count of vans for a company
     *
     * @return int
     */
    public static function getVansCountByCompanyId(int $companyId): int
    {
        return self::where('company_id', '=', $companyId)->count();
    }

    /**
     * Get total stock quantity across all vans for a company
     *
     * @return int
     */
    public static function getTotalStockByCompanyId(int $companyId): int
    {
        return self::where('company_id', $companyId)
            ->join('van_products', 'vans.id', '=', 'van_products.van_id')
            ->sum('van_products.quantity');
    }

    /**
     * Get count of active operatives for today
     * Operatives who have recorded usage during the current day
     *
     * @return int
     */
    public static function getActiveOperativesCountToday(int $companyId): int
    {
        $vans = self::where('company_id', '=', $companyId)->get();

        if ($vans->isEmpty()) {
            return 0;
        }

        $vanIds = $vans->pluck('id');

        return VanOperativeProductUsage::whereIn('van_id', $vanIds)
            ->whereDate('used_at', '=', now()->toDateString())
            ->distinct('van_id')
            ->count('van_id');
    }

    /**
     * Get total stock value across all vans for a company
     * Combined monetary value of all inventory (£)
     *
     * @return float
     */
    public static function getTotalStockValue(int $companyId): float
    {
        $vans = self::where('company_id', '=', $companyId)->get();

        if ($vans->isEmpty()) {
            return 0.0;
        }

        $vanIds = $vans->pluck('id');

        return VanProduct::whereIn('van_id', $vanIds)
            ->selectRaw('SUM(price * quantity) as total_value')
            ->value('total_value') ?? 0.0;
    }
    /**
     * Get count of low stock items across all vans for a company
     * Items that have fallen below the minimum stock threshold
     *
     * @return int
     */

    public static function getLowStockAlertsCount(int $companyId): int
    {
        $vans = self::where('company_id', '=', $companyId)->get();

        if ($vans->isEmpty()) {
            return 0;
        }

        $vanIds = $vans->pluck('id');

        // Critical = quantity > 0 AND quantity <= min_threshold
        return VanProduct::whereIn('van_id', $vanIds)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<', 'min_threshold')
            ->count();
    }
    /**
     * Get count of pending orders across all vans for a company
     * Orders that have been placed but not yet fulfilled or delivered
     *
     * @return int
     */
    public static function getPendingOrdersCount(int $companyId): int
    {
        $vans = self::where('company_id', '=', $companyId)->get();

        if ($vans->isEmpty()) {
            return 0;
        }

        $vanIds = $vans->pluck('id');

        // Pending status = 'pending'
        return VanInventoryOrder::whereIn('van_id', $vanIds)
            ->where('order_status', '=', OrderStatusEnum::PENDING->value)
            ->count();
    }

    /**
     * Get stock value breakdown by van for a company
     * Returns array with van name and stock value
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getStockValueByVan(int $companyId): \Illuminate\Support\Collection
    {
        $vans = self::where('company_id', '=', $companyId)->get();

        if ($vans->isEmpty()) {
            return collect();
        }
        return $vans->map(function ($van) {
            $stockValue = VanProduct::where('van_id', $van->id)
                ->selectRaw('SUM(price * quantity) as total_value')
                ->value('total_value') ?? 0;

            return [
                'van_id' => $van->id,
                'van_name' => $van->user_name,
                'operative' => $van->operative,
                'number_plate' => $van->number_plate,
                'stock_value' => (float) $stockValue,
            ];
        })->sortByDesc('stock_value')->values();
    }

// Add after getStockValueByVan method

    /**
     * Get all vans for a company (for dropdown)
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getVansForCompany(int $companyId): \Illuminate\Support\Collection
    {
        return self::where('company_id', '=', $companyId)
            ->select('id', 'user_name', 'operative', 'number_plate')
            ->get();
    }

    /**
     * Get stock usage data for a van
     * Returns daily usage value in (£)
     *
     * @param int $vanId
     * @param string $period (daily|weekly)
     * @return \Illuminate\Support\Collection
     */
    public static function getStockUsageByVan(int $vanId, string $period = 'daily'): \Illuminate\Support\Collection
    {
        $query = VanOperativeProductUsage::where('van_id', $vanId)
            ->with(['vanProduct:id,price,product_name']);

        if ($period === 'daily') {
            $query->whereDate('used_at', '>=', now()->subDays(30));
            $groupBy = 'DATE(used_at)';
        } else {
            $query->whereDate('used_at', '>=', now()->subWeeks(12));
            $groupBy = 'YEAR(used_at), WEEK(used_at)';
        }

        return $query->get()
            ->map(function ($usage) {
                $price = $usage->vanProduct->price ?? 0;
                return [
                    'date' => $usage->used_at->format('Y-m-d'),
                    'quantity_used' => $usage->quantity_used,
                    'price' => $price,
                    'value' => $usage->quantity_used * $price,
                    'job_reference' => $usage->job_reference,
                ];
            })
            ->groupBy('date')
            ->map(function ($dayGroup) {
                return [
                    'date' => $dayGroup->first()['date'],
                    'total_quantity' => $dayGroup->sum('quantity_used'),
                    'total_value' => $dayGroup->sum('value'),
                ];
            })
            ->values();
    }

    /**
     * Get total stock usage value for a van
     *
     * @param int $vanId
     * @return float
     */
    public static function getTotalUsageValue(int $vanId): float
    {
        $usages = VanOperativeProductUsage::where('van_id', $vanId)
            ->with(['vanProduct:id,price'])
            ->get();

        return $usages->sum(function ($usage) {
            $price = $usage->vanProduct->price ?? 0;
            return $usage->quantity_used * $price;
        });
    }
}
