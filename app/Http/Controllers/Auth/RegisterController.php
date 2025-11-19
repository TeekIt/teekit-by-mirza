<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailServices;
use App\Services\JsonResponseServices;
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
            'unit_address' => 'nullable|string',
            'postcode' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ];

        if ($data['is_child_seller'] != 0) {
            $rules['parent_store'] = 'required|exists:users,business_name';
        }

        return Validator::make($data, $rules);
    }

    /**
     * register() function (It is only used for the registration of web users)
     * Create a new user instance after a valid registration.
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

        $validatedData = $validatedData->validated();

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

        $parentStoreId = (isset($validatedData['parent_store'])) ?
            User::getSellerByBusinessName($validatedData('parent_store'))->id :
            null;

        $user = User::createStore(
            $validatedData['name'],
            strtolower($validatedData['email']),
            $validatedData['password'],
            $validatedData['country_code'],
            $validatedData['phone'],
            $validatedData['address'],
            $validatedData['unit_address'],
            $validatedData['postcode'],
            $validatedData['country'],
            $validatedData['state'],
            $validatedData['city'],
            $validatedData['business_name'],
            $validatedData['business_phone'],
            $validatedData['lat'],
            $validatedData['lon'],
            $businessHours,
            isset($validatedData['parent_store']) ? UserRoleEnum::CHILD_SELLER : UserRoleEnum::SELLER,
            $parentStoreId
        );

        if ($user instanceof User) {
            echo 'User Created';

            EmailServices::sendNewSellerMail(
                $user,
                $user->role_id,
                ($user->role_id === UserRoleEnum::CHILD_SELLER) ? $validatedData['parent_store'] : null,
            );
        }
    }
}
