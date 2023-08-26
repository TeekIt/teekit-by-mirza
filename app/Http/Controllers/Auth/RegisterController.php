<?php

namespace App\Http\Controllers\Auth;

use App\Mail\StoreRegisterMail;
use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Laracasts\Flash\Flash;

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
        if ($data['checked_value'] != 0) {
            return Validator::make($data, [
                'name' => 'required|string|max:80',
                'email' => 'required|string|email|max:80|unique:users',
                'password' => 'required|string|min:8|max:50',
                'phone' => 'required|string|min:10|max:10',
                'company_name' => 'required|string|max:80',
                'company_phone' => 'required|string|min:10|max:10',
                'location_text' => 'required|string',
                'select_values' => 'required',
            ]);
        } else {
            return Validator::make($data, [
                'name' => 'required|string|max:80',
                'email' => 'required|string|email|max:80|unique:users',
                'password' => 'required|string|min:8|max:50',
                'phone' => 'required|string|min:10|max:10',
                'company_name' => 'required|string|max:80',
                'company_phone' => 'required|string|min:10|max:10',
                'location_text' => 'required|string',
            ]);
        }
    }

    /**
     * register_web function (It is only used for the registration of web users)
     * Create a new user instance after a valid registration.
     * @param array $data
     * @return User|\Illuminate\Http\RedirectResponse
     */
    protected function register(Request $request)
    {
        $is_valid = $this->validator($request->all());
        if ($is_valid->fails()) {
            return response()->json([
                'errors' => $is_valid->errors(),
            ], 200);
            exit;
        }
        $data = $request->toArray();
        $data['Address']['lat'] = $data['lat'];
        $data['Address']['lon'] = $data['lon'];
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
        if ($request->input('select_values')) {
            $parent = User::where('name', $request->input('select_values'))->first();
            $parent_store_id = $parent->id;
        }
        $User = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => '+44' . $data['phone'],
            'address_1' => $data['location_text'],
            'business_name' => $data['company_name'],
            'business_phone' => '+44' . $data['company_phone'],
            'business_location' => json_encode($data['Address']),
            'lat' => $data['Address']['lat'],
            'lon' => $data['Address']['lon'],
            'business_hours' => $business_hours,
            'settings' => '{"notification_music": 1}',
            'role_id' => $request->input('select_values') ? 5 : 2,
            'parent_store_id' => $request->input('select_values') ? $parent_store_id : NULL,
            'is_active' => 0,
        ]);
        if ($User) {
            echo "User Created";
        }
        $verification_code = Crypt::encrypt($User->email);
        $FRONTEND_URL = env('FRONTEND_URL');
        $account_verification_link = $FRONTEND_URL . '/auth/verify?token=' . $verification_code;
        if ($request->input('select_values')) {
            $parent_store = $request->input('select_values');
            $html = '<html>
            Hi, Team Teek IT.<br><br>
            ' .  $parent_store  . ' child store has signed up today.
            <br>
           Please verify their details and take your decision to allow or disallow the store on our platform.<br><br>
           <strong>Store Name:</strong> '  .  $User->business_name   .  '<br>
           <strong>Owner Name:</strong> '  .  $User->name   .  '<br>
           <strong>Email:</strong> '  .  $User->email  .  '<br>
           <strong>Parent Store:</strong> '  .  $parent_store  .  '<br>
           <strong>Contact:</strong> '  .  $User->business_phone  .  '<br>
           <strong>Address:</strong> '  .  $User->address_1  .  '
           <br><br>
            <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
            ' . $account_verification_link . '
            <br><br><br>
        </html>';
        } else {
            $html = '<html>
            Hi, Team Teek IT.<br><br>
           A new store signed up today.
            <br>
           Please verify their details and take your decision to allow or disallow the store on our platform.<br><br>
           <strong>Store Name:</strong> '  .  $User->business_name   .  '<br>
           <strong>Owner Name:</strong> '  .  $User->name   .  '<br>
           <strong>Email:</strong> '  .  $User->email  .  '<br>
           <strong>Contact:</strong> '  .  $User->business_phone  .  '<br>
           <strong>Address:</strong> '  .  $User->address_1  .  '
           <br><br>
            <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
            ' . $account_verification_link . '
            <br><br><br>
        </html>';
        }
        $subject = env('APP_NAME') . ': Account Verification';
        Mail::to(config('constants.ADMIN_EMAIL'))
            ->send(new StoreRegisterMail($html, $subject));
        Mail::to('mirzaabdullahizhar.teekit@gmail.com')
            ->send(new StoreRegisterMail($html, $subject));

        $admin_users = Role::with('users')->where('name', 'superadmin')->first();
        $store_link = $FRONTEND_URL . '/customer/' . $User->id . '/details';
        $admin_subject = env('APP_NAME') . ': New Store Registered';
        foreach ($admin_users->users as $user) {
            $adminHtml = '<html>
            Hi, ' . $user->name . '<br><br>
            A new store has been register to your site  ' . env('APP_NAME') . '.
            <br>
            Please click on below link to activate store. <br><br>
            <a href="' . $store_link . '">Verify</a> OR Copy This in your Browser
            ' . $store_link . '
            <br><br><br>
        </html>';
            if (!empty($adminHtml)) Mail::to($user->email)
                ->send(new StoreRegisterMail($adminHtml, $admin_subject));
        }
    }
}