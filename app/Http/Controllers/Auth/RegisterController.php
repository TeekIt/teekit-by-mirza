<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use App\Services\EmailManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => 'required|string|max:80',
            'email' => 'required|string|email|max:80|unique:users',
            'password' => 'required|string|min:8|max:50',
            'phone' => 'required|string|min:10|max:10',
            'business_name' => 'required|string|max:80|unique:users,business_name',
            'business_phone' => 'required|string|min:10|max:10',
            'address' => 'required|string',
            'postcode' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string'
        ];
    
        if ($data['checked_value'] != 0) $rules['parent_store'] = 'required|exists:users,business_name';
    
        return Validator::make($data, $rules);
    }

    /**
     * register_web function (It is only used for the registration of web users)
     * Create a new user instance after a valid registration.
     * @param array $data
     * @return User|\Illuminate\Http\RedirectResponse
     */
    protected function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 200);
        }
        $data = $request->toArray();
        $business_hours = '{
            "time": {
                "Monday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Tuesday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Wednesday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Thursday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Friday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Saturday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                },
                "Sunday": {
                    "open": null,
                    "close": null,
                    "closed": "on"
                }
            },
            "submitted" : null
        }';
        $parent_store_id = ($request->input('parent_store')) ? User::getStoreByBusinessName($request->input('parent_store'))->id : null;
        $user = User::createStore(
            $data['name'],
            strtolower($data['email']),
            $data['password'],
            $data['phone'],
            $data['address'],
            $data['unit_address'],
            $data['postcode'],
            $data['country'],
            $data['state'],
            $data['city'],
            $data['business_name'],
            $data['business_phone'],
            $data['lat'],
            $data['lon'],
            $business_hours,
            $request->input('parent_store') ? 5 : 2,
            $parent_store_id
        );

        if ($user) {
            echo "User Created";
        }

        // 2: parent store
        ($user->role_id === 2) ? EmailManagement::sendNewParentStoreMail($user) : EmailManagement::sendNewChildStoreMail($user, $request->input('parent_store'));

        // $admin_users = Role::with('users')->where('name', 'superadmin')->first();
        // $store_link = $FRONTEND_URL . '/customer/' . $user->id . '/details';
        // $admin_subject = env('APP_NAME') . ': New Store Registered';
        // foreach ($admin_users->users as $user) {
        //     $adminHtml = '<html>
        //     Hi, ' . $user->name . '<br><br>
        //     A new store has been register to your site  ' . env('APP_NAME') . '.
        //     <br>
        //     Please click on below link to activate store. <br><br>
        //     <a href="' . $store_link . '">Verify</a> OR Copy This in your Browser
        //     ' . $store_link . '
        //     <br><br><br>
        // </html>';
        //     if (!empty($adminHtml)) Mail::to($user->email)
        //         ->send(new StoreRegisterMail($adminHtml, $admin_subject));
        // }
    }
}