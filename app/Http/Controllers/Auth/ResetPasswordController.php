<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
     * It will get the reset the password 
     * @version 1.3.0
     */
    public function reset(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validate->fails()) {
            $response = array('data' => $validate->messages(), 'status' => false, 'message' => 'Validation error');
            return response()->json($response, 400);
        }
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $user = User::where('email', $request->email)->first();
        if (!empty($user)) {
            if ($request->token == $user->temp_code) {
                $user->update(['password' => Hash::make($request->password)]);
                $response = array('status' => true, 'message' => 'Password is updated successfully.');
                return response()->json($response, 200);
            } else {
                $response = array('status' => false, 'message' => 'Password reset token is invalid.');
                return response()->json($response, 400);
            }
        }
        $response = array('status' => false, 'message' => 'Please provide valid email.');
        return response()->json($response, 400);
        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        // return $response == Password::PASSWORD_RESET
        // ? $this->sendResetResponse($response)
        // : $this->sendResetFailedResponse($request, $response);
    }
}