<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanInventory extends Model
{
    /** @use HasFactory<\Database\Factories\VanInventoryFactory> */
    use HasFactory;
    protected $table = 'van_inventories';

    protected $fillable = [
        'product_name',
        'qty',
        'price'
    ];
}
