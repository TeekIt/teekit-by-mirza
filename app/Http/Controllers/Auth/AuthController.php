<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ModelDisabledStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuyerResource;
use App\Jobs\SendStripeConnectAccMailJob;
use App\Models\JwtToken;
use App\Models\User;
use App\Services\EmailServices;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register For Mobile App
     *
     * @author Huzaifa Haleem
     */
    public function registerBuyer(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:50',
            'countryCode' => 'required|string|max:4',
            'phone' => 'required|string|max:13',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }
        $validatedData = (object) $validatedData->validated();

        $user = User::createBuyer(
            $validatedData->name,
            $validatedData->l_name,
            $validatedData->email,
            $validatedData->password,
            $validatedData->countryCode,
            $validatedData->phone,
            User::ACTIVE,
            Str::uuid()
        );

        EmailServices::sendBuyerAccVerificationMail($user);

        return response()->json([
            'status' => config('constants.TRUE_STATUS'),
            'role' => 'buyer',
            'message' => 'You have registered succesfully! We have sent a verification link to your email address. Please click on the link to activate your account.',
        ], config('constants.HTTP_OK'));
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (! $token = JWTAuth::attempt($credentials)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.INVALID_CREDENTIALS'),
                config('constants.HTTP_UNAUTHORIZED')
            );
        }

        $user = JWTAuth::user();
        if ($user->email_verified_at == null) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.EMAIL_NOT_VERIFIED'),
                config('constants.HTTP_UNAUTHORIZED')
            );
        }

        if ($user->is_active == User::BLOCK) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.ACCOUNT_DEACTIVATED'),
                config('constants.HTTP_UNAUTHORIZED')
            );
        }

        $this->saveJWT($request, $user, $token);

        return $this->respondWithToken($token);
    }

    public function verify(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return response($validatedData->errors()->first(), config('constants.HTTP_INVALID_ARGUMETS'));
        }

        $validatedData = (object) $validatedData->validated();

        $email = Crypt::decrypt($validatedData->token);
        $user = User::where('email', '=', $email)->first();

        if (! $user) {
            return response('Invalid verification token', config('constants.HTTP_UNAUTHORIZED'));
        }

        if ($user->email_verified_at != null) {
            return response('Account already verified', config('constants.HTTP_OK'));
        }

        $user->email_verified_at = now();
        $user->is_active = User::ACTIVE;
        $user->save();

        if (in_array($user->role_id, [UserRoleEnum::SELLER->value, UserRoleEnum::CHILD_SELLER->value])) {
            SendStripeConnectAccMailJob::dispatch($user)->onQueue('high');
        }

        return response('Account successfully verified', config('constants.HTTP_OK'));
    }

    /**
     * It will update the password
     *
     * @version 1.0.0
     */
    public function changePassword(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $user = JWTAuth::user();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                'Password changed successfully.',
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            'User not found.',
            config('constants.HTTP_UNPROCESSABLE_REQUEST')
        );
    }

    /**
     *  It will Get the authenticated User.
     */
    public function me()
    {
        $user = JWTAuth::user();
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'l_name' => $user->l_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address_1' => $user->address_1,
            'address_2' => $user->address_2,
            'postal_code' => $user->postal_code,
            'business_name' => $user->business_name,
            'business_phone' => $user->business_phone,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'bank_details' => $user->bank_details,
            'user_img' => $user->user_img,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'is_online' => $user->is_online,
            'last_login' => $user->last_login,
            'roles' => $user->role()->pluck('name'),
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * It will Logout the buyer
     * (Invalidate the token).
     *
     * @version 1.0.0
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            'Successfully logged out.',
            config('constants.HTTP_OK')
        );
    }

    /**
     * It will Refresh a token.
     *
     * @version 1.0.0

     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * It will Get the token array structure.
     *
     * @param  string  $token
     *
     * @version 1.0.0
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = JWTAuth::user();

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'l_name' => $user->l_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'postal_code' => $user->postcode,
            'address_1' => $user->full_address,
            'address_2' => $user->unit_address,
            'is_online' => $user->is_online,
            'last_login' => $user->last_login,
            'roles' => $user->role()->pluck('name'),
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            config('constants.LOGIN_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }

    protected function saveJWT($request, $user, $token)
    {
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        $agent = new Agent;
        $isDesktop = $agent->isDesktop();
        $isPhone = $agent->isPhone();

        $jwtToken = new JwtToken;
        $jwtToken->user_id = $user->id;
        $jwtToken->token = $token;
        $jwtToken->browser = $agent->browser();
        $jwtToken->platform = $agent->platform();
        $jwtToken->device = $agent->device();
        $mobileHeader = $request->header('x_platform');

        if (isset($mobileHeader) && $mobileHeader == 'mobile') {
            JwtToken::where('user_id', '=', $user->id)->where('phone', 1)->delete();
            $jwtToken->phone = 1;
            $jwtToken->save();
        } else {
            JwtToken::where('user_id', '=', $user->id)->where('desktop', 1)->delete();
            $jwtToken->desktop = 1;
            $jwtToken->save();
        }
    }

    /**
     * It will update user status
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.1.0
     */
    public function updateStatus(Request $request)
    {
        $user = User::find(Auth::id());
        $user->is_online = $request->is_online;
        $user->save();

        return $this->me();
    }

    /**
     * Get user details w.r.t 'id'
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.4.0
     */
    public function getUserDetails($userId)
    {
        $data = User::getUserInfo($userId);
        /*
         * Just creating this variable so we don't have to call the "empty()" function again & again
         * Because it will increase the API response time
         */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            ($dataIsEmpty) ? config('constants.HTTP_UNPROCESSABLE_REQUEST') : config('constants.HTTP_OK')
        );
    }

    /**
     * Listing of all SECRET KEYS
     *
     * @version 1.0.0
     */
    // public function keys()
    // {
    //     return response()->json([
    //         'data' => Keys::all(),
    //         'status' => config('constants.TRUE_STATUS'),
    //         'message' => ''
    //     ], config('constants.HTTP_OK'));
    // }
    /**
     * It will delete user from users table by id
     * It will insert the deleted user data into 'Deleted_users' table
     *
     * @version 1.0.0
     */
    public function deleteUser()
    {
        $authUserId = User::getAuthUser()->id;
        if ($authUserId) {
            $user = User::find($authUserId);

            DB::table('deleted_users')->insert([
                'user_id' => $user->id,
                'postcode' => $user->postcode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->forceDelete();

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.ITEM_DELETED'),
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.NO_RECORD'),
            config('constants.HTTP_OK')
        );
    }

    /**
     * Google register
     *
     * @version 1.0.0
     */
    public function registerBuyerFromGoogle(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string',
            'l_name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|max:5',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $buyer = User::create([
            'name' => $request->name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'address_1' => $request->address_1,
            'lat' => $request->lat,
            'lon' => $request->lon,
            'postcode' => $request->postcode,
            'contact' => $request->contact,
            'role_id' => UserRoleEnum::BUYER,
        ]);

        $buyer = User::getBuyerByEmail($buyer->email);
        $token = JWTAuth::fromUser($buyer);

        return JsonResponseServices::getApiResponse(
            [
                'user' => new BuyerResource($buyer),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
            config('constants.TRUE_STATUS'),
            config('constants.REGISTER_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }

    /**
     * Google login via email
     *
     * @version 1.0.0
     */
    public function loginBuyerFromGoogle(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
        if ($validatedData->fails()) {
            JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $buyer = User::getBuyerByEmail($validatedData->email);
        if (! $buyer) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.INVALID_CREDENTIALS'),
                config('constants.HTTP_UNAUTHORIZED')
            );
        }
        $token = JWTAuth::fromUser($buyer);

        return JsonResponseServices::getApiResponse(
            [
                'user' => new BuyerResource($buyer),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
            config('constants.TRUE_STATUS'),
            config('constants.REGISTER_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
}
