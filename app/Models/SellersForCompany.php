<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellersForCompany extends Model
{
    /** @use HasFactory<\Database\Factories\SellersForCompanyFactory> */
    use HasFactory;

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
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Helpers
     */
    public static function addBulk(array $data): bool
    {
        return self::insert($data);
    }

    public static function deleteByCompanyAndSellerId(int $companyId, int $sellerId): bool
    {
        return self::where('company_id', '=', $companyId)->where('seller_id', '=', $sellerId)->forceDelete();
    }
}
