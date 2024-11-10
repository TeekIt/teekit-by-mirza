<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestBuyer extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'country_code',
        'phone',
        'full_address',
        'unit_address',
        'country',
        'state',
        'city',
        'postcode',
        'lat',
        'lon',
    ];
    /**
     * Relations
     */
    public function productsByBuyer(): MorphMany
    {
        return $this->morphMany(ProductsByBuyer::class, 'created_by');
    } 
    /**
     * Helpers
     */
    public static function addOrUpdate(
        string $fName,
        string $lName,
        string $email,
        string $countryCode,
        string $phone,
        ?string $fullAddress = null,
        ?string $unitAddress = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?string $postcode = null,
        ?float $lat = null,
        ?float $lon = null
    ): self {
        return self::updateOrCreate(
            ['email' => $email],
            [
                'f_name' => $fName,
                'l_name' => $lName,
                'country_code' => $countryCode,
                'phone' => $phone,
                'full_address' => $fullAddress,
                'unit_address' => $unitAddress,
                'country' => $country,
                'state' => $state,
                'city' => $city,
                'postcode' => $postcode,
                'lat' => $lat,
                'lon' => $lon,
            ]
        );
    }    
}
