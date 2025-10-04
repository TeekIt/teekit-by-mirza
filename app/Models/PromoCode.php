<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'promo_code',
        'discount_type',
        'discount',
        'order_number',
        'usage_limit',
        'min_amnt_for_discount',
        'max_amnt_for_discount',
        'store_id',
        'free_delivery',
        'expiry_dt',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];
    /**
     * Relations
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function promoCodesUsageLimit(): HasMany
    {
        return $this->hasMany(PromoCodesUsageLimit::class, 'promo_code_id');
    }
    /**
     * Helpers
     */
    public static function getAll(string $orderBy, array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)->orderBy('created_at', $orderBy)->paginate(10);
    }

    public static function getByPromoCode(string $promoCode, ?int $customerId = null, array $columns = ['*']): PromoCode
    {
        return self::with(['seller:id,business_name'])
            ->when(!is_null($customerId), function ($query) use ($customerId) {
                $query->with(['promoCodesUsageLimit' => function ($promoCodesUsageLimitRelation) use ($customerId) {
                    $promoCodesUsageLimitRelation->where('customer_id', '=', $customerId);
                }]);
            })
            ->where('promo_code', '=', $promoCode)
            ->firstOrFail();
    }

    public static function addOrUpdate(array $data, ?int $id = null): PromoCode|bool
    {
        return DB::transaction(function () use ($data, $id) {
            $newRecord = (is_null($id)) ? true : false;

            $promoCode = $newRecord ? new self : self::findOrFail($id);

            $data['free_delivery'] = (isset($data['free_delivery'])) ? 1 : 0;

            return $newRecord ? self::create($data) : $promoCode->update($data);
        });
    }
}
