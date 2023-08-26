<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\ProductsController;
use App\Products;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UsersController;
use App\Keys;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use JWTAuth;
use Jenssegers\Agent\Agent;
use App\Models\JwtToken;
use App\Services\JsonResponseCustom;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'verify', 'searchSellerProducts', 'loginGoogle', 'registerGoogle']]);
    }
    /**
     * Register For Mobile App
     * @author Huzaifa Haleem
     * @version 1.9.0
     */
    public function register(Request $request)
    {
        try {
            $validate = User::validator($request);
            if ($validate->fails()) {
                $response = array('data' => $validate->messages(), 'status' => false, 'message' => config('constants.VALIDATION_ERROR'));
                return response()->json($response, 400);
            }
            $User = User::create([
                'name' => $request->name,
                'l_name' => $request->l_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'business_name' => $request->business_name,
                'business_location' => $request->business_location,
                'lat' => json_decode($request->business_location)->lat,
                'lon' => json_decode($request->business_location)->lon,
                'seller_id' => $request->seller_id,
                'postcode' => $request->postal_code,
                'is_active' => ($request->get('role') == 'buyer') ? 1 : 0,
                'role_id' => 3,
                // 'vehicle_type' => $request->has('vehicle_type') ? $request->vehicle_type : null
                'referral_code' => Str::uuid(),
            ]);
            if ($User) {
                if ($request->hasFile('user_img')) {
                    $User->user_img = User::uploadImg($request);
                    $User->save();
                }
            }

            $account_verification_link = url('/') . '/auth/verify?token=' . Crypt::encrypt($User->email);
            $html = '<html>
            Congratulations ' . $User->name . '!<br><br>
            You have successfully registered on ' . env('APP_NAME') . '.
            <br>
            There is just one more step to go. Click on the link below to verify your account so you can start purchasing products on TeekIT today!  <br><br>
                <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
                ' . $account_verification_link . '
            <br><br><br>
            For more information please visit https://teekit.co.uk/
            If you have any further inquiries please email admin@teekit.co.uk
            </html>';

            Mail::send('emails.general', ["html" => $html], function ($message) use ($request, $User) {
                $message->to($request->email, $User->name)
                    ->subject(env('APP_NAME') . ': Account Verification');
            });
            $response = array('status' => true, 'role' => $request->role, 'message' => 'You have registered succesfully! We have sent a verification link to your email address. Please click on the link to activate your account.');
            return response()->json($response, 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.INVALID_CREDENTIALS')], 401);
            }
            $user = JWTAuth::user();
            if ($user->email_verified_at == null) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.EMAIL_NOT_VERIFIED')], 401);
            }
            if ($user->is_active == 0) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.ACCOUNT_DEACTIVATED')], 401);
            }
            $this->authenticated($request, $user, $token);
            return $this->respondWithToken($token);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
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
                'status' => false,
                'message' => $validate->errors()
            ], 422);
        }

        $token = $request->token;

        $verification_token = Crypt::decrypt($request->token);

        $user = User::where('email', $verification_token)->first();
        $email_verified_at = Carbon::now();

        if ($user) {
            if ($user->email_verified_at != null) {
                echo "Account Already verified";
                return;
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => 'Account Already verified'
                ], 200);
            }
            $user->email_verified_at = $email_verified_at;
            $user->is_active = 1;
            $user->save();

            echo "Account successfully verified";
            return;

            return response()->json([
                'data' => [],
                'status' => true,
                'message' => 'Account successfully verified'
            ], 200);
        } else {
            echo "Invalid verification token";
            return;

            return response()->json([
                'data' => [],
                'status' => false,
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
                'status' => false,
                'message' =>  $validate->errors()
            ], 422);
        }

        $User = JWTAuth::user();
        if ($User) {
            $User->password = Hash::make($request->password);
            $User->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' =>  'Password changed successfully.'
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' =>  'User not found.'
            ], 404);
        }
    }

    /**
     *  It will Get the authenticated User.
     * @version 1.0.0
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = JWTAuth::user();
        $user = User::find($user->id);
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
            'roles' => $user->role()->pluck('name'),
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        );
        return response()->json([
            'data' => $data_info,
            'status' => true,
            'message' => config('constants.DATA_UPDATED_SUCCESS')
        ], 200);
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
            'status' => true,
            'message' =>  'Successfully logged out.'
        ], 200);
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
            'status' => true,
            'message' =>  config('constants.LOGIN_SUCCESS')
        ], 200);
    }
    // /**
    //  * Fetch seller/store information w.r.t ID
    //  * If seller/store have distance it will return distance
    //  * @author Mirza Abdullah Izhar
    //  * @version 2.1.0
    //  */
    // public function getSellerInfo($seller_info, $result = null)
    // {
    //     $user = $seller_info;
    //     if (!$user) return null;
    //     $data_info = array(
    //         'id' => $user->id,
    //         'name' => $user->name,
    //         'email' => $user->email,
    //         'address_1' => $user->address_1,
    //         'business_name' => $user->business_name,
    //         'business_location' => $user->business_location,
    //         'business_hours' => $user->business_hours,
    //         'user_img' => $user->user_img,
    //         'pending_withdraw' => $user->pending_withdraw,
    //         'total_withdraw' => $user->total_withdraw,
    //         'parent_store_id' => $user->parent_store_id,
    //         'is_online' => $user->is_online,
    //         'roles' => ($user->role_id == 2) ? ['sellers'] : ['child_sellers']
    //     );
    //     if (!is_null($result)) {
    //         $data_info['distance'] = $result['distance'];
    //         $data_info['duration'] = $result['duration'];
    //     }
    //     return $data_info;
    // }
    /**
     * It will get user via given id
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function get_user($user_id)
    {
        return UsersController::getSellerInfo(User::find($user_id));
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
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function updateUser(Request $request)
    {
        $validate = User::updateValidator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
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
            //return response()->json($response, 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }
    /**
     * It will update user status
     * @author Mirza Abdullah Izhar
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
    /**
     * Search products w.r.t Seller/Store 'id' & Product Name
     * @author Mirza Abdullah Izhar
     * @version 1.4.0
     */
    public function searchSellerProducts($seller_id, $product_name)
    {
        try {
            $user = User::find($seller_id);
            $data = [];
            $article = Products::search($product_name)
                ->where('user_id', $user->id)
                ->where('status', 1);
            $products = $article->paginate(20);
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                foreach ($products as $product) {
                    $data[] = (new ProductsController())->getProductInfo($product->id);
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
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
                'status' => true,
                'message' => ''
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Get user details w.r.t 'id'
     * @author Mirza Abdullah Izhar
     * @version 1.4.0
     */
    public function getUserDetails($user_id)
    {
        $data = User::getUserInfo($user_id);
        return JsonResponseCustom::getApiResponse(
            (empty($data)) ? [] : $data,
            (empty($data)) ? false : true,
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
            $keys = Keys::all();
            return response()->json([
                'data' => $keys,
                'status' => true,
                'message' => ''
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
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
            // $validatedData = Validator::make($request->all(), [
            //     'user_id' => 'required|integer'
            // ]);
            // if ($validatedData->fails()) {
            //     return response()->json([
            //         'data' => [],
            //         'status' => false,
            //         'message' => $validatedData->errors()
            //     ], 422);
            // }
            // $user = User::find($request->user_id);
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
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            }
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Google register
     * @version 1.0.0
     */
    public function registerGoogle(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string',
                'l_name' => 'required|string',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => 'required|string|max:255',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
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
                'status' => true,
                'message' => config('constants.REGISTER_SUCCESS'),
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Google login via email
     * @version 1.0.0
     */
    public function loginGoogle(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }
            $user = User::where('email', '=', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'data' => [],
                    'status' => false,
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
                'status' => true,
                'message' =>   config('constants.LOGIN_SUCCESS'),
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    // public function getDistanceBetweenPointsNew($destination_lat, $destination_lon, $origin_lat, $origin_lon)
    // {
    //     $destination_address = $destination_lat . ',' . $destination_lon;
    //     $origing_address = $origin_lat . ',' . $origin_lon;
    //     /* Rameesha's URL */
    //     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";

    //     $results = json_decode(file_get_contents($url), true);  
    //     $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
    //     $distanceInMiles = (double)$meters[0] * 0.000621;

    //     $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
    //     $durationInMinutes = round((int)$durationInSeconds[0] / 60); 
    //     return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    // }

    // public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
    // {
    //     $address1 = $latitude1 . ',' . $longitude1;
    //     $address2 = $latitude2 . ',' . $longitude2;

    //     /* Old URL */
    //     // $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($address1) . "&destination=" . urlencode($address2) . "&transit_routing_preference=fewer_transfers&departure_time=" . time() . "&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";

    //     /* Rameesha's URL */
    //     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($address1) . "&destinations=" . urlencode($address2) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";

    //     $query = file_get_contents($url);
    //     $results = json_decode($query, true);
    //     // dd($results);
    //     /* Old Variables */
    //     // $distanceString = explode(' ', $results['routes'][0]['legs'][0]['distance']['text']);
    //     // $distanceInMiles = (Double)$distanceString[0] * 0.621371;
    //     /* New Variables */
    //     $distanceString = explode(' ', $results['rows'][0]['elements'][0]['distance']['text']);
    //     // dd($results);
    //     // $distanceInMiles = (float)$distanceString[0] * 0.621371;

    //     /* Old Variables */
    //     // $durationInSeconds = $results['routes'][0]['legs'][0]['duration']['value'];
    //     // $durationInMinutes = round($durationInSeconds / 60);
    //     /* New Variables */
    //     $durationInMinutes = explode(' ', $results['rows'][0]['elements'][0]['duration']['text']);
    //     // $durationInMinutes = round($durationInSeconds / 60);

    //     // return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    //     return ['distance' => $distanceString, 'duration' => $durationInMinutes];
    // }
}
