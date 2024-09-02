<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCodes extends Model
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
        'expiry_dt'
    ];
}