<?php

namespace App\Http\Controllers\Api\v1;

use App\Drivers;
use App\DriverDocuments;
use App\Http\Controllers\Controller;
use App\Orders;
use App\Services\EmailManagement;
use App\Services\JsonResponseCustom;
use App\User;
use App\VerificationCodes;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class DriverController extends Controller
{
    /**
     * It will fetch & show the driver's 
     * info
     * @version 1.0.0
     */
    public function info($id)
    {
        return User::where('id', $id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'delivery_boy');
            })->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->l_name,
                    'lat_lon' => $user->business_location,
                    'phone' => $user->phone
                ];
            });
    }

    /**
     * It will add driver location 
     * @version 1.0.0
     */
    public function addLatLon(Request $request)
    {
        $data = [
            'business_location' => $request->latlon,
            'lat' => json_decode($request->latlon)->lat,
            'lon' => json_decode($request->latlon)->long
        ];
        return User::where('id', auth()->id())
            ->update($data);
    }
    /**
     * It will submit driver's withdrawl 
     * @version 1.0.0
     */
    public function submitWithdrawal(Request $request)
    {
        if (!\auth()->guard('rider')->user()) abort(404);
        $user = User::find(\auth()->id());
        if (empty($user->bank_details)) {
            return response()->json(['message' => 'Please update your bank account info.'], 403);
        }
        if ($request->has('amount')) {
            if ($user->pending_withdraw < $request->amount) {
                return response()->json(['message' => 'Requested value exceeds your current balance.'], 403);
            }
        }
        $withdrawal = $request->has('amount') ? $request->amount : $user->pending_withdraw;
        $user->pending_withdraw = $user->pending_withdraw - $withdrawal;
        $user->total_withdraw = $user->total_withdraw + $withdrawal;
        $with = new WithdrawalRequests();
        $with->user_id = \auth()->id();
        $with->amount = $withdrawal;
        $with->status = 'Pending';
        $with->bank_detail = $user->bank_details;
        $with->save();
        $user->save();
        return response()->json([
            'data' => [],
            'status' => true,
            'message' => config("constants.WITHDRAWAL_REQUEST_SUBMITTED")
        ], 200);
    }
    /**
     * It will fetch & show the driver's 
     * Withdrawal balance
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function getWithdrawalBalance()
    {
        $amount = number_format((float)\auth()->guard('rider')->user()->pending_withdraw, 2, '.', '');
        return response()->json([
            'data' => $amount,
            'status' => true,
            'message' => ""
        ], 200);
    }
    /**
     * It will submit driver's bank account details 
     * @version 1.0.0
     */
    public function submitBankAccountDetails(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'branch_code' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'phone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => false,
                'message' => ""
            ], 422);
        }
        $data = ['branch' => $request->branch_code, 'bank_name' => $request->bank_name, 'account_number' => $request->account_number, 'phone' => $request->phone];
        $bankDetails = [1 => $data];
        auth()->user()->update(['bank_details' => json_encode($bankDetails)]);
        return response()->json([
            'data' => [],
            'status' => true,
            'message' => config("constants.BANK_DETAILS_UPDATED")
        ], 200);
    }
    /**
     * It will show drivers withdrawl requests 
     * @version 1.0.0
     */
    public function driverAllWithdrawalRequests()
    {

        if (!auth()->guard('rider')->user()) {
            abort(404);
        }
        // $user_id = Auth::id();
        $user_id = auth()->guard('rider')->user()->id;
        $withdrawals = WithdrawalRequests::where('user_id', '=', $user_id)
            ->orderByDesc('created_at')->get();
        $data = array();
        foreach ($withdrawals as $key => $withdrawal) {
            $data[$key]['id'] = $withdrawal->id;
            $data[$key]['user_id'] = $withdrawal->user_id;
            $data[$key]['amount'] = $withdrawal->amount;
            $data[$key]['status'] = $withdrawal->status;
            $data[$key]['bank_detail'] = json_decode($withdrawal->bank_detail);
            $data[$key]['created_at'] = $withdrawal->created_at;
            $data[$key]['updated_at'] = $withdrawal->updated_at;
            $data[$key]['transaction_id'] = $withdrawal->transaction_id;
        }
        return response()->json([
            'data' => $data,
            'status' => true,
            'message' => ""
        ], 200);
    }
    /**
     * It will confirm that either the entered verification code
     * By the driver is correct or not
     * If it is correct then the 'order_status' & 'delivery_status'
     * Will be marked as 'complete'
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function checkVerificationCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'verification_code' => 'required|min:6|max:6',
            'delivery_boy_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => false,
                'message' => config('constants.MISSING_OR_INVALID_DATA')
            ], 422);
        } else {
            $verification_codes = VerificationCodes::query()->select('code->code as verification_code')
                ->where('order_id', '=', $request->order_id)
                ->get();
            $saved_code = $verification_codes[0]->verification_code;
            $given_code = $request->verification_code;
            // If the driver is failed to enter the right verification code
            if ($saved_code != $given_code) {
                VerificationCodes::where('order_id', '=', $request->order_id)
                    ->update(['code->driver_failed_to_enter_code' => 'Yes']);
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.VERIFICATION_FAILED')
                ], 200);
            } else {
                VerificationCodes::where('order_id', '=', $request->order_id)
                    ->update(['code->driver_failed_to_enter_code' => 'No']);
                Orders::where('id', '=', $request->order_id)->update(['order_status' => 'complete', 'delivery_status' => 'complete']);
                // $driver = User::find($request->delivery_boy_id);
                $driver = Drivers::find($request->delivery_boy_id);
                $order = Orders::find($request->order_id);
                $driver->pending_withdraw = $order->driver_charges + $driver->pending_withdraw;
                $driver->save();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.VERIFICATION_SUCCESS')
                ], 200);
            }
        }
    }
    /**
     * If the driver does not have the verfication code 
     * Then this function will be used to 
     * Update 'code->driver_failed_to_enter_code' column &
     * It will mark the 'delivery_status' as 'complete'
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function driverFailedToEnterCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => false,
                'message' => config('constants.MISSING_OR_INVALID_DATA')
            ], 422);
        } else {
            // Update table
            DB::table('verification_codes')
                ->where('order_id', $request->order_id)
                ->update(['code->driver_failed_to_enter_code' => "Yes"]);
            /* Because the driver was not able to enter the code due to some reasons but still he has delivered the product. Therefore we will mark the 'delivery_status' as 'complete' & 'order_status' as 'delivered' */
            Orders::where('id', '=', $request->order_id)->update(['order_status' => 'delivered', 'delivery_status' => 'complete']);

            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_UPDATED')
            ], 200);
        }
    }
    /**
     * Driver signUp
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function registerDriver(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'f_name' => 'required|string|max:80',
                'l_name' => 'required|string|max:80',
                'email' => 'required|string|email|max:80|unique:drivers',
                'phone' => 'required|string|min:10|max:10',
                'password' => 'required|string|min:8|max:50',
                'profile_img' => 'image|max:1000',
                'vehicle_type' => 'required|int',
                'area' => 'required|string',
                'account_holders_name' => 'required|string',
                'bank_name' => 'required|string',
                'sort_code' => 'required|min:6|max:6',
                'account_number' => 'required|min:8|max:8',
                'front_img' => 'required|image|max:1200',
                'back_img' => 'required|image|max:1200'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            // First add the newly signed-up driver
            $drivers = Drivers::add($request);
            // If the driver has provided a profile img then upload it
            if ($request->hasFile('profile_img')) Drivers::addImg($drivers, $request, 'profile_img');
            // Now upload driver documents
            DriverDocuments::add($request, $drivers->id);
            // Send verification email
            EmailManagement::sendDriverAccVerificationMail($drivers);
            if ($drivers) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.TRUE_STATUS'),
                    config('constants.DRIVER_REGISTERATION_MSG'),
                    config('constants.HTTP_OK')
                );
            }
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.REGISTER_FAILED'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('rider')->factory()->getTTL() * 60
        ]);
    }
    /**
     * Driver logIn
     * @version 1.1.0
     */
    protected function loginDriver(Request $request)
    {
        $validatedData = \Validator::make($request->all(), [
            'email' => 'required|string|email|max:80',
            'password' => 'required|string|min:8|max:50'
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $validatedData->errors()
            ], 422);
        }
        try {
            $credentials = request(['email', 'password']);
            $driver_info = [];
            $driver_info = Drivers::where('email', $credentials['email'])->first();
            if (Hash::check($credentials['password'], $driver_info->password)) {
                $token = auth('rider')->attempt($credentials);
                $data_info = array(
                    'id' => $driver_info->id,
                    'f_name' => $driver_info->f_name,
                    'l_name' => $driver_info->l_name,
                    'email' => $driver_info->email,
                    'phone' => $driver_info->phone,
                    'profile_img' => $driver_info->profile_img,
                    'vehicle_type' => $driver_info->vehicle_type,
                    'vehicle_number' => $driver_info->vehicle_number,
                    'area' => $driver_info->area,
                    'lat' => $driver_info->lat,
                    'lon' => $driver_info->lon,
                    'account_holders_name' => $driver_info->account_holders_name,
                    'bank_name' => $driver_info->bank_name,
                    'sort_code' => $driver_info->sort_code,
                    'account_number' => $driver_info->account_number,
                    'driving_licence_name' => $driver_info->driving_licence_name,
                    'dob' => $driver_info->dob,
                    'driving_licence_number' => $driver_info->driving_licence_number,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                );
                return response()->json([
                    'data' => $data_info,
                    'status' => true,
                    'message' =>  config('constants.LOGIN_SUCCESS')
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' =>  config('constants.INVALID_CREDENTIALS')
                ], 401);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.INVALID_CREDENTIALS')
            ], 401);
        }
    }
}