<?php

namespace App;

use App\Enums\UserRole;
use App\Models\CommissionAndServiceFee;
use App\Services\EmailServices;
use App\Models\ReferralCodeRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
use Illuminate\Pagination\LengthAwarePaginator;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory, SoftDeletes;
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
        'stripe_account_id',
        'last_login',
        'email_verified_at',
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
        ];
    }
    /**
     * Relations
     */
    public function commissionAndServiceFee(): HasOne
    {
        return $this->hasOne(CommissionAndServiceFee::class, 'seller_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class);
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
     * Scopes
     */
    public function scopeWhereUserIsActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }
    /**
     * Helpers
     */
    public static function getSellerCommonColumns(): array
    {
        return [
            'users.id',
            'users.business_name',
            'users.business_hours',
            'users.full_address',
            'users.country',
            'users.state',
            'users.city',
            'users.lat',
            'users.lon',
            'users.user_img',
        ];
    }

    public static function adminUsersDel(Request $request)
    {
        for ($i = 0; $i < count($request->users); $i++) self::findOrfail($request->users[$i])->delete();
    }

    public static function updateInfo(
        int $id,
        string $name = null,
        string $l_name = null,
        string $email = null,
        string $phone = null,
        string $business_name = null,
        string $business_phone = null,
        string $password = null,
        array $hours = [],
        string $user_img = null,
        string $stripe_account_id = null
    ): bool {
        $user = self::findOrFail($id);
        if (!is_null($name)) $user->name = $name;
        if (!is_null($l_name)) $user->l_name = $l_name;
        if (!is_null($email)) $user->email = $email;
        if (!is_null($phone)) $user->phone = '+44' . $phone;
        if (!is_null($business_name)) $user->business_name = $business_name;
        if (!is_null($business_phone)) $user->business_phone = '+44' . $business_phone;
        if (!is_null($password)) $user->password = Hash::make($password);
        if (!empty($hours)) $user->business_hours = json_encode($hours);
        if (!is_null($user_img)) $user->user_img = $user_img;
        if (!is_null($stripe_account_id)) $user->stripe_account_id = $stripe_account_id;
        return $user->save();
    }

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
        $user = self::findOrFail($user_id);
        $user->full_address = $full_address;
        if (!is_null($unit_address))
            $user->unit_address = $unit_address;
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
    ): self {
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
            'role_id' => UserRole::BUYER,
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
        UserRole $role_id,
        int|null $parent_store_id = null
    ): self {
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

    public static function getParentAndChildSellersList(array $columns): Collection
    {
        return self::select($columns)
            ->WhereUserIsActive()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('role_id', [UserRole::SELLER, UserRole::CHILD_SELLER])
            ->orderBy('business_name', 'asc')
            ->get();
    }

    public static function getParentAndChildSellersByCity(string $city): LengthAwarePaginator
    {
        return self::WhereUserIsActive()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('city', $city)
            ->whereIn('role_id', [UserRole::SELLER, UserRole::CHILD_SELLER])
            ->orderBy('business_name', 'asc')
            ->paginate(10);
    }

    public static function getParentAndChildSellersByState(string $state): Collection
    {
        return self::WhereUserIsActive()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('state', $state)
            ->whereIn('role_id', [UserRole::SELLER, UserRole::CHILD_SELLER])
            ->orderBy('business_name', 'asc')
            ->get();
    }

    public static function getParentSellersSpecificColumns(array $columns): Collection
    {
        return self::select($columns)
            ->where('role_id', UserRole::SELLER)
            ->get();
    }

    public static function getParentSellers(string $search = ''): LengthAwarePaginator
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', UserRole::SELLER)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getChildSellers(string $search = ''): LengthAwarePaginator
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', UserRole::CHILD_SELLER)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getCustomers(string $search = ''): LengthAwarePaginator
    {
        return self::where('name', 'like', '%' . $search . '%')
            ->where('role_id', UserRole::BUYER)
            ->orderByDesc('created_at')
            ->paginate(9);
    }

    public static function getAllCustomers(): Collection
    {
        return self::where('role_id', UserRole::BUYER)->get();
    }

    public static function getBuyersWithReferralCode(): LengthAwarePaginator
    {
        return self::where('role_id', UserRole::BUYER)->whereNotNull('referral_code')->paginate(10);
    }

    public static function getBuyerByEmail(string $email, array $columns = ['*']): ?User
    {
        return self::select($columns)->where('email', $email)->where('role_id', UserRole::BUYER)->first();
    }

    public static function getStoreByBusinessName(string $business_name): ?User
    {
        return self::where('business_name', $business_name)->first();
    }

    public static function getUserByID(int $id, array $columns = ['*']): ?User
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

    public static function activeOrBlockStore(int $id, int $status): bool
    {
        self::where('id', '=', $id)->update(['is_active' => $status]);
        if ($status == 1) {
            $user = self::findOrFail($id);
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
        return self::where('id', $user_id)->pluck('role_id');
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
        return (is_null($data)) ? false : $data;
    }

    public static function addIntoWallet(int $user_id, float $amount)
    {
        return self::where('id', $user_id)->increment('pending_withdraw', $amount);
    }

    public static function deductFromWallet(int $user_id, float $amount)
    {
        return self::where('id', $user_id)->decrement('pending_withdraw', $amount);
    }

    public static function getSellerID(): int
    {
        return auth()->user()->id;
    }
}
