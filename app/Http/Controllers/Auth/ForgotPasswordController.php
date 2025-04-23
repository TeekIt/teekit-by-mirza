<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
// use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        /* We will send the password reset link to this user. Once we have attempted
        to send the link, we will examine the response then see the message we
        need to show to the user. Finally, we'll send out a proper response. */
        $response = $this->broker()->sendResetLink(
            $this->credentials($request),
            function ($request) {
                $user = User::where('email', $request->email)->first();

                if ($user) {
                    $token = Str::random(60);

                    DB::table('password_resets')->updateOrInsert(
                        ['email' => $request->email],
                        [
                            'email' => $request->email,
                            'token' => Hash::make($token),
                            'created_at' => now(),
                        ]
                    );
                       
                    $user->sendPasswordResetNotification($token);
                } else {
                    return back()->withErrors(['email' => trans('passwords.user')]);
                }
            }
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
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
