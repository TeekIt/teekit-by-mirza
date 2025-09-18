<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

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

    public static function getAll(string $orderBy, array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)->orderBy('created_at', $orderBy)->paginate(10);
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
