<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use App\Enums\UserRoleEnum;
use App\Models\CommissionAndServiceFee;
use App\Models\ReferralCodeRelation;
use App\Notifications\CustomResetPasswordNotification;
use App\Services\EmailServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Billable, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Custom Properties
     */
    public const ACTIVE = 1;

    public const BLOCK = 0;

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

    public function qty(): HasMany
    {
        return $this->hasMany(Qty::class, 'seller_id');
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

    /**
     * Scopes
     */
    public function scopeWhereUserIsActive(Builder $query): void
    {
        $query->where('is_active', self::ACTIVE);
    }

    public function scopeWhereUserIsBlocked(Builder $query): void
    {
        $query->where('is_active', self::BLOCK);
    }

    public function scopeWhereRoleIsParentOrChildSeller(Builder $query): void
    {
        $query->whereIn('role_id', [UserRoleEnum::SELLER, UserRoleEnum::CHILD_SELLER]);
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

    public static function isSuperAdmin(): bool
    {
        return self::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN->value;
    }

    public static function isParentSeller(): bool
    {
        return self::getAuthUser()->role_id === UserRoleEnum::SELLER->value;
    }

    public static function isChildSeller(): bool
    {
        return self::getAuthUser()->role_id === UserRoleEnum::CHILD_SELLER->value;
    }

    public static function isCompany(): bool
    {
        return self::getAuthUser()->role_id === UserRoleEnum::COMPANY->value;
    }

    public static function adminUsersDel(Request $request)
    {
        for ($i = 0; $i < count($request->users); $i++) {
            self::findOrfail($request->users[$i])->delete();
        }
    }

    public static function updateInfo(
        int $id,
        ?string $name = null,
        ?string $lName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $fullAddress = null,
        ?string $unitAddress = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?string $postcode = null,
        ?string $lat = null,
        ?string $lon = null,
        ?string $businessName = null,
        ?string $businessPhone = null,
        ?string $password = null,
        ?array $hours = [],
        ?string $userImg = null,
        ?string $stripeAccountId = null
    ): bool {
        $user = self::findOrFail($id);

        if (! is_null($name)) {
            $user->name = $name;
        }
        if (! is_null($lName)) {
            $user->l_name = $lName;
        }
        if (! is_null($email)) {
            $user->email = $email;
        }
        if (! is_null($phone)) {
            $user->phone = '+44' . $phone;
        }
        if (! is_null($fullAddress)) {
            $user->full_address = $fullAddress;
        }
        if (! is_null($unitAddress)) {
            $user->unit_address = $unitAddress;
        }
        if (! is_null($country)) {
            $user->country = $country;
        }
        if (! is_null($state)) {
            $user->state = $state;
        }
        if (! is_null($city)) {
            $user->city = $city;
        }
        if (! is_null($postcode)) {
            $user->postcode = $postcode;
        }
        if (! is_null($lat)) {
            $user->lat = $lat;
        }
        if (! is_null($lon)) {
            $user->lon = $lon;
        }
        if (! is_null($businessName)) {
            $user->business_name = $businessName;
        }
        if (! is_null($businessPhone)) {
            $user->business_phone = '+44' . $businessPhone;
        }
        if (! is_null($password)) {
            $user->password = Hash::make($password);
        }
        if (! empty($hours)) {
            $user->business_hours = json_encode($hours);
        }
        if (! is_null($userImg)) {
            $user->user_img = $userImg;
        }
        if (! is_null($stripeAccountId)) {
            $user->stripe_account_id = $stripeAccountId;
        }

        return $user->save();
    }

    public static function updateStoreLocation(
        int $user_id,
        string $full_address,
        ?string $unit_address,
        string $country,
        string $state,
        string $city,
        string $postcode,
        string $lat,
        string $lon
    ): bool {
        $user = self::findOrFail($user_id);
        $user->full_address = $full_address;
        if (! is_null($unit_address)) {
            $user->unit_address = $unit_address;
        }
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
        string $lastName,
        string $email,
        string $password,
        string $countryCode,
        string $phoneNumber,
        int $isActive,
        string $referralCode
    ): self {
        return self::create([
            'name' => $name,
            'l_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
            'country_code' => $countryCode,
            'phone' => $phoneNumber,
            'country' => 'NA',
            'state' => 'NA',
            'city' => 'NA',
            'is_active' => $isActive,
            'role_id' => UserRoleEnum::BUYER,
            'referral_code' => $referralCode,
        ]);
    }

    public static function add(
        string $name,
        string $email,
        string $password,
        string $countryCode,
        string $phone,
        string $address,
        ?string $unit_address,
        string $postcode,
        string $country,
        string $state,
        string $city,
        string $business_name,
        string $business_phone,
        float $lat,
        float $lon,
        string $business_hours,
        UserRoleEnum $role_id,
        ?int $parent_store_id = null
    ): self {
        return self::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'country_code' => $countryCode,
            'phone' => $phone,
            'business_name' => $business_name,
            'business_phone' => $business_phone,
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
            'is_active' => self::BLOCK,
            'role_id' => $role_id,
            'parent_store_id' => $parent_store_id,
        ]);
    }

    public static function getParentAndChildSellers(array $columns): Collection
    {
        return self::select($columns)
            ->WhereUserIsActive()
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->orderBy('business_name', 'asc')
            ->get();
    }

    public static function getActiveAndBlockedParentAndChildSellersByCity(
        string $city,
        int $exceptSellerId,
        int $numberOfRows = 25
    ): Collection {
        $city = explode(' ', $city);
        
        return self::WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('city', $city)
            ->where('id', '!=', $exceptSellerId)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getActiveAndBlockedParentAndChildSellersByCityAndCategory(
        string $city,
        int $categoryId,
        int $exceptSellerId,
        int $numberOfRows = 25
    ): Collection {
        $city = explode(' ', $city);

        return self::whereHas('qty', function ($qtyRelation) use ($categoryId) {
            $qtyRelation->where('category_id', '=', $categoryId);
        })
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('city', $city)
            ->where('id', '!=', $exceptSellerId)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getActiveParentAndChildSellersByCityAndCategory(
        string $city,
        int $categoryId,
        int $exceptSellerId,
        int $numberOfRows = 25
    ): Collection {
        $city = explode(' ', $city);

        return self::WhereUserIsActive()
            ->whereHas('qty', function ($qtyRelation) use ($categoryId) {
                $qtyRelation->where('category_id', '=', $categoryId);
            })
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('city', $city)
            ->where('id', '!=', $exceptSellerId)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getBlokedParentAndChildSellersByCity(string $city, int $numberOfRows = 25): Collection
    {
        $city = explode(' ', $city);
        
        return self::WhereUserIsBlocked()
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('city', $city)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getActiveParentAndChildSellersByCity(string $city, int $numberOfRows = 25): Collection
    {
        $city = explode(' ', $city);

        return self::WhereUserIsActive()
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('city', $city)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getParentAndChildSellersByState(string $state, int $numberOfRows = 25): Collection
    {
        return self::WhereUserIsActive()
            ->WhereRoleIsParentOrChildSeller()
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('state', '=', $state)
            ->orderBy('business_name', 'asc')
            ->take($numberOfRows)
            ->get();
    }

    public static function getParentSellersSpecificColumns(array $columns): Collection
    {
        return self::select($columns)
            ->where('role_id', '=', UserRoleEnum::SELLER)
            ->get();
    }

    public static function getParentSellers(OrderByEnum $orderBy, string $search = ''): LengthAwarePaginator
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', '=', UserRoleEnum::SELLER)
            ->orderBy('created_at', $orderBy->value)
            ->paginate(9);
    }

    public static function getChildSellers(OrderByEnum $orderBy, string $search = ''): LengthAwarePaginator
    {
        return self::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', '=', UserRoleEnum::CHILD_SELLER)
            ->orderBy('created_at', $orderBy->value)
            ->paginate(9);
    }

    public static function getCustomers(string $search = ''): LengthAwarePaginator
    {
        return self::where('name', 'like', '%' . $search . '%')
            ->where('role_id', '=', UserRoleEnum::BUYER->value)
            ->orderByDesc('created_at')
            ->paginate(9);
    }

    public static function getAllCustomers(): Collection
    {
        return self::where('role_id', '=', UserRoleEnum::BUYER)->get();
    }

    public static function getBuyersWithReferralCode(): LengthAwarePaginator
    {
        return self::where('role_id', '=', UserRoleEnum::BUYER)->whereNotNull('referral_code')->paginate(10);
    }

    public static function getParentOrChildSellerByEmail(string $email, array $columns = ['*']): ?User
    {
        return self::select($columns)
            ->WhereRoleIsParentOrChildSeller()
            ->where('email', '=', $email)
            ->first();
    }

    public static function getBuyerByEmail(string $email, array $columns = ['*']): ?User
    {
        return self::select($columns)->where('email', '=', $email)->where('role_id', '=', UserRoleEnum::BUYER)->first();
    }

    public static function getSellerByBusinessName(string $businessName): ?User
    {
        return self::where('business_name', '=', $businessName)->first();
    }

    public static function getUserByID(int $id, array $columns = ['*']): User
    {
        return self::select($columns)->findOrFail($id);
    }

    public function nearbyUsers($user_lat, $user_lon, $radius): User
    {
        return self::selectRaw('*, (  3961 * acos( cos( radians(' . $user_lat . ') ) *
                                cos( radians(users.lat) ) *
                                cos( radians(users.lon) - radians(' . $user_lon . ') ) +
                                sin( radians(' . $user_lat . ') ) *
                                sin( radians(users.lat) ) ) )
                                AS distance')
            ->having('distance', '<', $radius)
            ->orderBy('distance', 'ASC')
            ->get();
    }

    public static function activeOrBlockSeller(int $id, int $status): bool
    {
        $updated = self::where('id', '=', $id)->update(['is_active' => $status]);

        if ($status == self::ACTIVE) {
            $user = self::findOrFail($id);
            EmailServices::sendSellerApprovedMail($user);
        }

        return $updated;
    }

    public static function activeOrBlockCustomer(int $id, int $status): int
    {
        return self::where('id', '=', $id)->update(['is_active' => $status]);
    }

    public static function getUserRole(int $id): SupportCollection
    {
        return self::where('id', '=', $id)->pluck('role_id');
    }

    public static function getUserInfo(int $id): ?array
    {
        $user = self::with('referralRelations')->where('id', '=', $id)->first();

        if ($user) {
            return [
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
                'referral_relation_details' => ($user->referralRelations) ? [$user->referralRelations] : null,
            ];
        }

        return null;
    }

    public static function verifyReferralCode(int $id, string $referral_code)
    {
        $data = self::where('id', '!=', $id)->where('referral_code', $referral_code)->first();

        return (is_null($data)) ? false : $data;
    }

    public static function addIntoWallet(int $id, float $amount)
    {
        return self::where('id', '=', $id)->increment('pending_withdraw', $amount);
    }

    public static function deductFromWallet(int $id, float $amount)
    {
        return self::where('id', '=', $id)->decrement('pending_withdraw', $amount);
    }

    public static function getAuthUser(): self
    {
        return auth()->user();
    }
}
