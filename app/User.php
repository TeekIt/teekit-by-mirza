<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use App\Mail\StoreRegisterMail;
use App\Models\ReferralCodeRelation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Validator;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_verified_at',
        'is_active',
        'name',
        'l_name',
        'email',
        'password',
        'phone',
        'address_1',
        'address_2',
        'postal_code',
        'business_name',
        'business_phone',
        'business_location',
        'business_hours',
        'settings',
        'bank_details',
        'user_img',
        'postal_code',
        'vehicle_type',
        'lat',
        'lon',
        'role_id',
        'parent_store_id',
        'referral_code'
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
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user');
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function seller()
    {
        return $this->belongsToMany('App\Role', 'role_user')->wherePivot('role_id', 2);
    }

    public function driver()
    {
        return $this->belongsToMany('App\Models\Role', 'role_user')->where('name', 'delivery_boy');
    }

    public function orders()
    {
        return $this->hasMany('App\Orders');
    }

    public function referralRelations()
    {
        return $this->hasOne(ReferralCodeRelation::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    /**
     * Validators
     */
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'l_name' => '',
            'postal_code' => '',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:50',
            'business_name' => 'string|max:255',
            'business_location' => 'string|max:255',
            'role' => 'required|string|max:255',
            'address_1' => '',
            'address_2' => '',
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'l_name' => '',
            'postal_code' => '',
            'business_name' => '',
            'business_location' => '',
            'user_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address_1' => '',
            'address_2' => ''
        ]);
    }
    /**
     * Helpers
     */
    public static function uploadImg(object $request)
    {
        $file = $request->file('user_img');
        $filename = uniqid($request->name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
        Storage::disk('spaces')->put($filename, File::get($file));
        if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
            info("file is store successfully : " . $filename);
        } else {
            info("file is not found :- " . $filename);
        }
        return $filename;
    }

    public static function getParentAndChildSellers()
    {
        return User::where('is_active', 1)
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->whereIn('role_id', [2, 5])
            ->orderBy('business_name', 'asc')
            ->paginate(10);
    }

    public static function getParentSellers(string $search = '')
    {
        return User::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', 2)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getChildSellers(string $search = '')
    {
        return User::where('business_name', 'like', '%' . $search . '%')
            ->where('role_id', 5)
            ->orderBy('business_name', 'asc')
            ->paginate(9);
    }

    public static function getCustomers(string $search = '')
    {
        return User::where('name', 'like', '%' .  $search . '%')
            ->where('role_id', 3)
            ->orderByDesc('created_at')
            ->paginate(9);
    }

    // This function will be removed once we have inserted referrals against all customers on our production
    public static function getBuyers(string $search = '')
    {
        return User::where('name', 'like', '%' .  $search . '%')
            ->where('role_id', 3)
            ->orderByDesc('created_at')
            ->get();
    }

    public static function getBuyersWithReferralCode()
    {
        return User::whereNotNull('referral_code')->paginate(10);
    }

    public static function getUserByID(int $user_id)
    {
        return User::find($user_id);
    }

    public function nearbyUsers($user_lat, $user_lon, $radius)
    {
        return User::selectRaw("*, (  3961 * acos( cos( radians(" . $user_lat . ") ) *
                                cos( radians(users.lat) ) *
                                cos( radians(users.lon) - radians(" . $user_lon . ") ) +
                                sin( radians(" . $user_lat . ") ) *
                                sin( radians(users.lat) ) ) )
                                AS distance")
            ->having("distance", "<", $radius)
            ->orderBy("distance", "ASC")
            ->get();
    }

    public static function sendStoreApprovedEmail(object $user)
    {
        $html = '<html>
            Hi, ' . $user->name . '<br><br>
            Thank you for registering on ' . env('APP_NAME') . '.
            <br>
            Your store has been approved. Please login to your
            <a href="' . url('/') . '">Store</a> to manage it.
            <br><br><br>
                     </html>';
        $subject = url('/') . ': Account Approved!';
        Mail::to($user->email)
            ->send(new StoreRegisterMail($html, $subject));
    }

    public static function activeOrBlockStore(int $user_id, int $status)
    {
        User::where('id', '=', $user_id)->update(['is_active' => $status]);
        if ($status == 1) {
            $user = User::findOrFail($user_id);
            static::sendStoreApprovedEmail($user);
        }
        return true;
    }

    public static function activeOrBlockCustomer(int $user_id, int $status)
    {
        return User::where('id', '=', $user_id)->update(['is_active' => $status]);
    }

    public static function getUserRole(int $user_id)
    {
        return  User::where('id', $user_id)->pluck('role_id');
    }

    public static function getUserInfo(int $user_id)
    {
        $user = User::with('referralRelations')->where('id', $user_id)->first();
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
        $data = User::where('id', '!=', $user_id)
        ->where('referral_code', $referral_code)->first();
        return (is_null($data)) ? false :  $data;
    }

    // public static function updateWalletAndStatus(int $status, float $bonus, int $user_id)
    // {
    //     $user = User::find($user_id);
    //     if ($user) {
    //         $user->pending_withdraw += $bonus;
    //         // $user->referral_useable = $status;
    //         $user->save();
    //     }
    //     return $user;
    // }

    public static function addIntoWallet(int $user_id, float $amount)
    {
        return User::where('id', $user_id)->increment('pending_withdraw', $amount);
    }

    public static function deductFromWallet(int $user_id, float $amount)
    {
        return User::where('id', $user_id)->decrement('pending_withdraw', $amount);
    }
}
