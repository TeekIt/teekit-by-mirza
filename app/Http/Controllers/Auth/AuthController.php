<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UsersController;
use App\Keys;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Jenssegers\Agent\Agent;
use App\Models\JwtToken;
use App\Services\EmailServices;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => [
            'loginBuyer',
            'registerBuyer',
            'verify',
            'searchSellerProducts',
            'loginBuyerFromGoogle',
            'registerBuyerFromGoogle'
        ]]);
    }
    /**
     * Register For Mobile App
     * @author Huzaifa Haleem
     */
    public function registerBuyer(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'l_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|max:50',
                'phone' => 'required|string|max:13'
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            $user = User::createBuyer(
                $request->name,
                $request->l_name,
                $request->email,
                $request->password,
                $request->phone,
                1,
                Str::uuid()
            );

            EmailServices::sendBuyerAccVerificationMail($user);

            return response()->json([
                'status' => config('constants.TRUE_STATUS'),
                'role' => 'buyer',
                'message' => 'You have registered succesfully! We have sent a verification link to your email address. Please click on the link to activate your account.'
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginBuyer(Request $request)
    {
        try {
            
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['data' => [], 'status' => config('constants.FALSE_STATUS'), 'message' => config('constants.INVALID_CREDENTIALS')], 401);
            }
            $user = JWTAuth::user();
            if ($user->email_verified_at == null) {
                return response()->json(['data' => [], 'status' => config('constants.FALSE_STATUS'), 'message' => config('constants.EMAIL_NOT_VERIFIED')], 401);
            }
            if ($user->is_active == 0) {
                return response()->json(['data' => [], 'status' => config('constants.FALSE_STATUS'), 'message' => config('constants.ACCOUNT_DEACTIVATED')], 401);
            }
            $this->authenticated($request, $user, $token);
            return $this->respondWithToken($token);
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }

    public function verify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validate->fails()) { {
                echo "Validation error";
                return;
            }
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' => $validate->errors()
            ], 422);
        }
        $verification_token = Crypt::decrypt($request->token);

        $user = User::where('email', $verification_token)->first();

        if ($user) {
            if ($user->email_verified_at != null) {
                echo "Account Already verified";
                return;
                return response()->json([
                    'data' => [],
                    'status' => config('constants.FALSE_STATUS'),
                    'message' => 'Account Already verified'
                ], config('constants.HTTP_OK'));
            }
            $user->email_verified_at = Carbon::now();
            $user->is_active = 1;
            $user->save();

            echo "Account successfully verified";
            return;

            return response()->json([
                'data' => [],
                'status' => config('constants.TRUE_STATUS'),
                'message' => 'Account successfully verified'
            ], config('constants.HTTP_OK'));
        } else {
            echo "Invalid verification token";
            return;

            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' => 'Invalid verification token'
            ], 401);
        }
    }
    /**
     * It will update the password
     * @version 1.0.0
     */
    public function changePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' =>  $validate->errors()
            ], 422);
        }

        $User = JWTAuth::user();
        if ($User) {
            $User->password = Hash::make($request->password);
            $User->save();
            return response()->json([
                'data' => [],
                'status' => config('constants.TRUE_STATUS'),
                'message' =>  'Password changed successfully.'
            ], config('constants.HTTP_OK'));
        } else {
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' =>  'User not found.'
            ], 404);
        }
    }

    /**
     *  It will Get the authenticated User.
     */
    public function me()
    {
        $user = JWTAuth::user();
        $data = array(
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
        );
        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
    /**
     * It will Log the user out
     * (Invalidate the token).
     * @version 1.0.0

     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json([
            'data' => [],
            'status' => config('constants.TRUE_STATUS'),
            'message' =>  'Successfully logged out.'
        ], config('constants.HTTP_OK'));
    }
    /**
     * It will Refresh a token.
     * @version 1.0.0

     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }
    /**
     * It will Get the token array structure.
     *
     * @param string $token
     * @version 1.0.0
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = JWTAuth::user();

        $url = URL::to('/');
        $imagePath = $user['user_img'];
        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'l_name' => $user->l_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'postal_code' => $user->postal_code,
            'address_1' => $user->address_1,
            'address_2' => $user->address_2,
            'is_online' => $user->is_online,
            'business_name' => $user->business_name,
            'business_phone' => $user->business_phone,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'bank_details' => $user->bank_details,
            'last_login' => $user->last_login,
            'roles' => $user->role()->pluck('name'),
            'user_img' => $imagePath,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'vehicle_type' => $user->vehicle_type,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        );
        return response()->json([
            'data' => $data_info,
            'status' => config('constants.TRUE_STATUS'),
            'message' =>  config('constants.LOGIN_SUCCESS')
        ], config('constants.HTTP_OK'));
    }

    protected function authenticated($request, $user, $token)
    {
        $olduser = $user;
        $user->last_login = date("Y-m-d H:i:s");
        $user->save();

        $agent = new Agent();
        $isDesktop = $agent->isDesktop();
        $isPhone = $agent->isPhone();
        $jwtToken = new JwtToken();
        $jwtToken->user_id = $user->id;
        $jwtToken->token = $token;
        $jwtToken->browser = $agent->browser();
        $jwtToken->platform = $agent->platform();
        $jwtToken->device = $agent->device();
        $mobileHeader = $request->header('x_platform');
        if (isset($mobileHeader) && $mobileHeader == 'mobile') {
            JwtToken::where('user_id', $user->id)->where('phone', 1)->delete();
            $jwtToken->phone = 1;
            $jwtToken->save();
        } else {
            JwtToken::where('user_id', $user->id)->where('desktop', 1)->delete();
            $jwtToken->desktop = 1;
            $jwtToken->save();
        }
    }
    /**
     * It will update user details
     * via given id
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function updateUser(Request $request)
    {
        $validate = User::updateValidator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' => $validate->messages()
            ], 422);
        }
        $user = JWTAuth::user();
        $User = User::find($user->id);
        if ($User) {
            $filename = $User->user_img;
            if ($request->hasFile('user_img')) {
                $file = $request->file('user_img');
                $filename = $file->getClientOriginalName();
                $filename = uniqid($User->id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                Storage::disk('user_public')->put($filename, File::get($file));
                if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                    info("file is store successfully : " . $filename);
                    $filename = "/user_imgs/" . $filename;
                } else {
                    info("file is not found :- " . $filename);
                }
            }
            $User->name = $request->name;
            $User->l_name = $request->l_name;
            $User->postal_code = $request->postal_code;
            $User->phone = $request->phone;
            $User->address_1 = $request->address_1;
            $User->address_2 = $request->address_2;
            $User->user_img = $filename;
            $User->save();
            $response = $this->me();
            return $response;
            //return response()->json($response, config('constants.HTTP_OK'));
        } else {
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' => 'User not found.'
            ], 404);
        }
    }
    /**
     * It will update user status
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function updateStatus(Request $request)
    {
        $user = User::find(Auth::id());
        $user->is_online = $request->is_online;
        $user->save();
        $response = $this->me();
        return $response;
    }

    public function deliveryBoys()
    {
        try {
            $users = User::query()->where('seller_id', '=', Auth::id())->get();
            $data = [];
            foreach ($users as $user) {
                if (Gate::allows('delivery_boy')) $data[] = UsersController::getSellerInfo($user);
            }
            return response()->json([
                'data' => $data,
                'status' => config('constants.TRUE_STATUS'),
                'message' => ''
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Get user details w.r.t 'id'
     * @author Muhammad Abdullah Mirza
     * @version 1.4.0
     */
    public function getUserDetails($user_id)
    {
        $data = User::getUserInfo($user_id);
        return JsonResponseServices::getApiResponse(
            (empty($data)) ? [] : $data,
            (empty($data)) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            (empty($data)) ? config('constants.NO_RECORD') : '',
            (empty($data)) ? config('constants.HTTP_UNPROCESSABLE_REQUEST') : config('constants.HTTP_OK')
        );
    }
    /**
     * Listing of all SECRET KEYS
     * @version 1.0.0
     */
    public function keys()
    {
        try {
            return response()->json([
                'data' => Keys::all(),
                'status' => config('constants.TRUE_STATUS'),
                'message' => ''
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will delete user from users table by id
     * It will insert the deleted user data into 'Deleted_users' table
     * @version 1.0.0
     */
    public function deleteUser(Request $request)
    {
        try {
            $user = User::find(Auth::id());
            if (!empty($user)) {
                DB::table('deleted_users')->insert([
                    'user_id' =>  $user->id,
                    'postcode' =>  $user->postcode,
                    'created_at' =>   Carbon::now(),
                    'updated_at' =>   Carbon::now()
                ]);
                $user->delete();
                return response()->json([
                    'data' => [],
                    'status' => config('constants.TRUE_STATUS'),
                    'message' => config('constants.ITEM_DELETED'),
                ], config('constants.HTTP_OK'));
            }
            return response()->json([
                'data' => [],
                'status' => config('constants.FALSE_STATUS'),
                'message' => config('constants.NO_RECORD')
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Google register
     * @version 1.0.0
     */
    public function registerBuyerFromGoogle(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string',
                'l_name' => 'required|string',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => 'required|string|max:5'
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }
            
            $user = User::create([
                'name' => $request->name,
                'l_name' => $request->l_name,
                'email' => $request->email,
                'address_1' => $request->address_1,
                'lat' => $request->lat,
                'lon' => $request->lon,
                'postcode' => $request->postcode,
                'contact' => $request->contact,
                'role_id' => 3,
            ]);
            $user = User::where('email', '=', $user->email)->first();
            $data_info = array(
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
                'roles' => [
                    'buyer'
                ],
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            );
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'data' => [
                    'user' => $data_info,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
                'status' => config('constants.TRUE_STATUS'),
                'message' => config('constants.REGISTER_SUCCESS'),
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Google login via email
     * @version 1.0.0
     */
    public function loginBuyerFromGoogle(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => config('constants.FALSE_STATUS'),
                    'message' => $validator->errors()
                ], 422);
            }
            $user = User::where('email', '=', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'data' => [],
                    'status' => config('constants.FALSE_STATUS'),
                    'message' =>  config('constants.INVALID_CREDENTIALS')
                ], 401);
            }
            $data_info = array(
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
                'roles' => [
                    'buyer'
                ],
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            );
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'data' => [
                    'user_id' => $data_info,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
                'status' => config('constants.TRUE_STATUS'),
                'message' =>   config('constants.LOGIN_SUCCESS'),
            ], config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
