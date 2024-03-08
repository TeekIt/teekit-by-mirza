<?php

namespace App;

use App\Services\ImageServices;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Drivers extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'phone',
        'password',
        'vehicle_type',
        'vehicle_number',
        'area',
        'lat',
        'lon',
        'account_holders_name',
        'bank_name',
        'sort_code',
        'account_number',
        'driving_licence_name',
        'dob',
        'driving_licence_number'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * Relations
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }
    /**
     * Helpers
     */
    public static function add(object $request)
    {
        return self::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => '+44' . $request->phone,
            'password' => Hash::make($request->password),
            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'area' => $request->area,
            'lat' => $request->lat,
            'lon' => $request->lon,
            'account_holders_name' => $request->account_holders_name,
            'bank_name' => $request->bank_name,
            'sort_code' => $request->sort_code,
            'account_number' => $request->account_number,
            'driving_licence_name' => $request->driving_licence_name,
            'dob' => $request->dob,
            'driving_licence_number' => $request->driving_licence_number
        ]);
    }

    public static function addImg(object $driver, object $request, string $img_key_name)
    {
        $driver->profile_img = ImageServices::uploadImg($request, $img_key_name, $driver->id);
        $driver->save();
        return $driver;
    }

    public static function getDrivers(string $search = '')
    {
        return self::where('f_name', 'like', '%' . $search . '%')
            ->orderBy('f_name', 'asc')
            ->paginate(9);
    }

    public function adminDriversDel(Request $request)
    {
        for ($i = 0; $i < count($request->drivers); $i++) {
            self::where('id', '=', $request->drivers[$i])->delete();
            DriverDocuments::where('driver_id', '=', $request->drivers[$i])->delete();
        }
        return response("Drivers Deleted Successfully");
    }
}
