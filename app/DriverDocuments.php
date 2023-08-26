<?php

namespace App;

use App\Services\ImageManipulation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverDocuments extends Model
{
    use SoftDeletes;
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
        return DriverDocuments::create([
            'driver_id' => $driver_id,
            'front_img' => ImageManipulation::uploadImg($request, 'front_img', $driver_id),
            'back_img' => ImageManipulation::uploadImg($request, 'back_img', $driver_id),
        ]);
    }
}
