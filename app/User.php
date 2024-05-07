<?php

namespace App;

use App\Services\EmailServices;
use App\Models\ReferralCodeRelation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'l_name',
        'email',
        'password',
        'phone',
        'business_name',
        'business_phone',
        'business_hours',
        'full_address',
        'unit_address',
        'country',
        'state',
        'city',
        'postcode',
        'lat',
        'lon',
        'bank_details',
        'settings',
        'user_img',
        'is_active',
        'is_online',
        'remember_token',
        'role_id',
        'pending_withdraw',
        'total_withdraw',
        'parent_store_id',
        'vehicle_type',
        'application_fee',
        'temp_code',
        'referral_code',
        'last_login',
        'email_verified_at'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
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
        return [
            'name' => $this->name,
            'roles' => $this->roles
        ];
    }
    /**
     * Relations
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('App\Role', 'role_user');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo('App\Role');
    }

    public function seller(): BelongsToMany
    {
        return $this->belongsToMany('App\Role', 'role_user')->wherePivot('role_id', 2);
    }

    // public function driver(): BelongsToMany
    // {
    //     return $this->belongsToMany('App\Models\Role', 'role_user')->where('name', 'delivery_boy');
    // }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Orders');
    }

    public function referralRelations(): HasOne
    {
        return $this->hasOne(ReferralCodeRelation::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Products::class);
    }
    /**
     * Validators
     */
    public static function validator(Request $request): object
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:50',
            'business_name' => 'string|max:255',
            'business_location' => 'string|max:255',
            // 'role' => 'required|string|max:255',
            'address_1' => 'required|string',
        ]);
    }

    public static function updateValidator(Request $request): object
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address_1' => 'required|string'
        ]);
    }
    /**
     * Helpers
     */
    public static function updateStoreLocation(
        int $user_id,
        string $full_address,
        string|null $unit_address,
        string $country,
        string $state,
        string $city,
        string $postcode,
        string $lat,
        string $lon
    ): bool {
        $user = self::find($user_id);
        $user->full_address = $full_address;
        if (!is_null($unit_address)) $user->unit_address = $unit_address;
        $user->country = $country;
        $user->state = $state;
        $user->city = $city;
        $user->postcode = $postcode;
        $user->lat = $lat;
        $user->lon = $lon;
        return $user->save();
    }

    public static function createBuyer(
        string $name,
        string $l_name,
        string $email,
        string $password,
        string $phone,
        int $is_active,
        string $referral_code
    ): object {
        return self::create([
            'name' => $name,
            'l_name' => $l_name,
            'email' => $email,
            'password' => Hash::make($password),
            'phone' => $phone,
            'country' => 'NA',
            'state' => 'NA',
            'city' => 'NA', 
            'is_active' => $is_active,
            'role_id' => 3,
            'referral_code' => $referral_code
        ]);
    }

    public static function createStore(
        string $name,
        string $email,
        string $password,
        string $phone,
        string $address,
        string|null $unit_address,
        string $postcode,
        string $country,
        string $state,
        string $city,
        string $business_name,
        string $business_phone,
        float $lat,
        float $lon,
        string $business_hours,
        int $role_id,
        int|null $parent_store_id = null
    ): object {
        return self::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'phone' => '+44' . $phone,
            'business_name' => $business_name,
            'business_phone' => '+44' . $business_phone,
            'business_hours' => $business_hours,
            'full_address' => $address,
            'unit_address' => $unit_address,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'postcode' => $postcode,
            'lat' => $lat,
            'lon' => $lon,
            'settings' => '{"notification_music": 1}',
            'is_active' => 0,
            'role_id' => $role_id,
            'parent_store_id' => $parent_store_id
        ]);
    }

    public static function getParentAndChildSellersByCity(string $city): object
    {
        return self::where('is_active', 1)
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('city', $city)
            ->whereIn('role_id', [2, 5])
            ->orderBy('business_name', 'asc')
            ->paginate(10);
    }

    public static function getParentAndChildSellersByState(string $state): object
    {
        return self::where('is_active', 1)
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('state', $state)
            ->whereIn('role_id', [2, 5])
            ->orderBy('business_name', 'asc')
            ->paginate(10);
    }

    public static function getParentSellersSpecificColumns(array $columns): object
    {
        return self::select($columns)
            ->where('role_id', 2)
            ->get();
    }

    public static function getParentSellers(string $search = ''): object
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', 2)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getChildSellers(string $search = ''): object
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', 5)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getCustomers(string $search = ''): object
    {
        return self::where('name', 'like', '%' .  $search . '%')
            ->where('role_id', 3)
            ->orderByDesc('created_at')
            ->paginate(9);
    }

    public static function getBuyersWithReferralCode(): object
    {
        return self::whereNotNull('referral_code')->paginate(10);
    }

    public static function getStoreByBusinessName(string $business_name): object
    {
        return self::where('business_name', $business_name)->first();
    }

    public static function getUserByID(int $id, array $columns): object
    {
        return self::select($columns)->find($id);
    }

    public function nearbyUsers($user_lat, $user_lon, $radius): object
    {
        return self::selectRaw("*, (  3961 * acos( cos( radians(" . $user_lat . ") ) *
                                cos( radians(users.lat) ) *
                                cos( radians(users.lon) - radians(" . $user_lon . ") ) +
                                sin( radians(" . $user_lat . ") ) *
                                sin( radians(users.lat) ) ) )
                                AS distance")
            ->having("distance", "<", $radius)
            ->orderBy("distance", "ASC")
            ->get();
    }

    public static function activeOrBlockStore(int $user_id, int $status): bool
    {
        self::where('id', '=', $user_id)->update(['is_active' => $status]);
        if ($status == 1) {
            $user = self::findOrFail($user_id);
            EmailServices::sendStoreApprovedMail($user);
        }
        return true;
    }

    public static function activeOrBlockCustomer(int $user_id, int $status): int
    {
        return self::where('id', '=', $user_id)->update(['is_active' => $status]);
    }

    public static function getUserRole(int $user_id): object
    {
        return  self::where('id', $user_id)->pluck('role_id');
    }

    public static function getUserInfo(int $user_id): array|null
    {
        $user = self::with('referralRelations')->where('id', $user_id)->first();
        if ($user) {
            return array(
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'business_name' => $user->business_name,
                'business_location' => $user->business_location,
                'address_1' => $user->address_1,
                'pending_withdraw' => $user->pending_withdraw,
                'total_withdraw' => $user->total_withdraw,
                'is_online' => $user->is_online,
                'roles' => $user->role()->pluck('name'),
                'user_img' => $user->user_img,
                'referral_code' => $user->referral_code,
                'referral_relation_details' => ($user->referralRelations) ? [$user->referralRelations] : null
            );
        }
        return null;
    }

    public static function verifyReferralCode(int $user_id, string $referral_code)
    {
        $data = User::where('id', '!=', $user_id)->where('referral_code', $referral_code)->first();
        return (is_null($data)) ? false :  $data;
    }

    public static function addIntoWallet(int $user_id, float $amount)
    {
        return self::where('id', $user_id)->increment('pending_withdraw', $amount);
    }

    public static function deductFromWallet(int $user_id, float $amount)
    {
        return self::where('id', $user_id)->decrement('pending_withdraw', $amount);
    }

    public static function  getSellerID(): int
    {
        return auth()->user()->id;
    }
}
