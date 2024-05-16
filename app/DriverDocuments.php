<?php

namespace App;

use App\Services\ImageServices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverDocuments extends Model
{
    use HasFactory, SoftDeletes;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'driver_id',
        'front_img',
        'back_img'
    ];
    /**
     * Relations
     */
    // 

    /**
     * Helpers
     */
    public static function add(object $request, int $driver_id)
    {
        return self::create([
            'driver_id' => $driver_id,
            'front_img' => ImageServices::uploadImg($request, 'front_img', $driver_id),
            'back_img' => ImageServices::uploadImg($request, 'back_img', $driver_id),
        ]);
    }
}
