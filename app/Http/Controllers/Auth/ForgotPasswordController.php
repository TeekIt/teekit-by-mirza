<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
use Mail;
use Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

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
     * It will get the reset email token 
     * @version 1.3.0
     */
    public function getResetToken(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'data' => $validate->messages(),
                'status' => false,
                'message' => config('constants.VALIDATION_ERROR')
            ], 400);
        }
        $user = User::where('email', $request->get('email'))->first();
        if (!$user) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => trans('passwords.user')
            ], 404);
        }
        $digits = 6;
        $token = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

        $user->temp_code = $token;
        $user->save();

        $html = '<html>
                Hi, ' . $user->name . '<br><br>

                You have requested to reset password on ' . env('APP_NAME') . '.

                Here is your Password reset Code. <br><br> <code style="background:lightgray">' . $token . '</code>
            </html>';

        Mail::send('emails.general', ["html" => $html], function ($message) use ($request, $user) {
            $message->to($request->email, $user->name)
                ->subject(env('APP_NAME') . ': Password Reset');
        });

        return response()->json(['status' => true, 'message' => 'Password reset link sent on your email.'], 200);
    }
}