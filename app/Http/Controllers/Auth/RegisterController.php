<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Services\EmailServices;
use App\Services\JsonResponseServices;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => 'required|string|max:80',
            'email' => 'required|string|email|max:80|unique:users',
            'password' => 'required|string|min:8|max:50',
            'country_code' => 'required|string',
            'phone' => 'required|string|min:8',
            'business_name' => 'required|string|max:80|unique:users,business_name',
            'business_phone' => 'required|string|min:8',
            'address' => 'required|string',
            'postcode' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
        ];

        if ($data['is_child_seller'] != 0) {
            $rules['parent_store'] = 'required|exists:users,business_name';
        }

        return Validator::make($data, $rules);
    }

    /**
     * register_web function (It is only used for the registration of web users)
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User|\Illuminate\Http\RedirectResponse
     */
    protected function register(Request $request)
    {
        $validatedData = $this->validator($request->all());
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $validatedData->errors(),
                config('constants.HTTP_OK')
            );
        }

        $data = $request->toArray();

        $businessHours = '{
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

        $parentStoreId = ($request->input('parent_store')) ? User::getSellerByBusinessName($request->input('parent_store'))->id : null;

        $user = User::createStore(
            $data['name'],
            strtolower($data['email']),
            $data['password'],
            $data['country_code'],
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
            $businessHours,
            $request->input('parent_store') ? UserRoleEnum::CHILD_SELLER : UserRoleEnum::SELLER,
            $parentStoreId
        );

        if ($user) {
            echo 'User Created';

            EmailServices::sendNewSellerMail(
                $user,
                $user->role_id,
                ($user->role_id === UserRoleEnum::CHILD_SELLER) ? $request->input('parent_store') : null,
            );
        }
    }
}
