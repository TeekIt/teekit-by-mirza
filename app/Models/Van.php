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

    public function vanOperativeProductUsages(): HasMany
    {
        return $this->hasMany(VanOperativeProductUsage::class, 'van_id');
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

    public static function getVansCountByCompanyId(int $companyId): int
    {
        return self::where('company_id', '=', $companyId)->count();
    }

    public static function getTotalStockByCompanyId(int $companyId): int
    {
        return self::where('company_id', '=', $companyId)
            ->join('van_products', 'vans.id', '=', 'van_products.van_id')
            ->sum('van_products.quantity');
    }

    public static function getActiveOperativesCount(int $companyId, string $date): int
    {
        return self::where('company_id', '=', $companyId)
            ->select('vans.id')
            ->whereHas('vanOperativeProductUsages', function ($query) use ($date) {
                $query->whereDate('used_at', $date);
            })
            ->distinct()
            ->count();
    }

    public static function getTotalStockValue(int $companyId): float
    {
        return self::where('vans.company_id', '=', $companyId)
            ->join('van_products', 'vans.id', '=', 'van_products.van_id')
            ->selectRaw('SUM(van_products.price * van_products.quantity) as total_stock_value')
            ->value('total_stock_value') ?? 0.0;
    }

    public static function getLowStockAlertsCount(int $companyId): int
    {
        return self::where('vans.company_id', '=', $companyId)
            ->join('van_products', 'vans.id', '=', 'van_products.van_id')
            ->where('van_products.quantity', '>', 0)
            ->whereColumn('van_products.quantity', '<', 'van_products.min_threshold')
            ->count();
    }

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

    public static function getStockUsageByVan(int $vanId, string $period = 'daily'): \Illuminate\Support\Collection
    {
        $query = VanOperativeProductUsage::with(['vanProduct:id,price,product_name'])->where('van_id', '=', $vanId);

        if ($period === 'daily') {
            $query->whereDate('used_at', '>=', now()->subDays(30));
        } else {
            $query->whereDate('used_at', '>=', now()->subWeeks(12));
        }

        return $query->get()
            ->map(function ($usage) {
                $price = $usage->vanProduct->price;
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

    public static function getTotalUsageValue(int $vanId): float
    {
        $usages = VanOperativeProductUsage::where('van_id', '=', $vanId)
            ->with(['vanProduct:id,price'])
            ->get();

        return $usages->sum(function ($usage) {
            $price = $usage->vanProduct->price ?? 0;
            return $usage->quantity_used * $price;
        });
    }
}
