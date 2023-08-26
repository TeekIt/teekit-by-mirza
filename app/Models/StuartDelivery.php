<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StuartDelivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'job_id'
    ];
}
