<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryOrderItem extends Model
{
     use SoftDeletes;
     use HasFactory;

    protected $table = 'inventory_order_items';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // 🔗 Relationships

    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItems::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function van()
    {
        return $this->belongsTo(Van::class);
    }

    public static function getVanInventoryOrders(int $perPage = 10)
    {
        return self::with(['order', 'product', 'seller'])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(fn ($item) => $item->order);
    }

    
}
