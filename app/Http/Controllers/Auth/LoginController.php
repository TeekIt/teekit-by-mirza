<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laracasts\Flash\Flash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }
    /**
     * It will log the user in
     * @version 1.3.0
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        // This section is the only change
        if ($this->guard()->validate($this->credentials($request))) {
            $user = $this->guard()->getLastAttempted();
            // Make sure the user is active
            if ($user->is_active && $this->attemptLogin($request) && $user->email_verified_at != null) {
                // Send the normal successful login response
                if (Gate::allows('seller') || Gate::allows('child_seller') || Gate::allows('superadmin')) {
                    return $this->sendLoginResponse($request);
                } else {
                    return redirect()
                        ->route('login')
                        ->withInput($request->only($this->username(), 'remember'))
                        ->withErrors(['active' => 'WARNING! You cannot access private pages.']);
                }
            } else {
                // Increment the failed login attempts and redirect back to the
                // login form with an error message.
                $this->incrementLoginAttempts($request);
                if ($user->email_verified_at == null) {
                    Flash::message('Email not verified, please verify your email first.');
                } else {
                    Flash::message('Your account is not activated yet, kindly contact the admin.');
                }

                return redirect()
                    ->route('login')
                    ->withInput($request->only($this->username(), 'remember'))
                    ->withErrors(['active' => 'You must be active to login.']);
            }
        }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        //return $this->sendFailedLoginResponse($request);
        return $this->invalidCreds($request);
    }
    /**
     * It is a helper method being called in login method
     * it will throw a flash message on incorrect/invalid credentials
     * @version 1.0.0
     */
    protected function invalidCreds()
    {
        throw ValidationException::withMessages([
            flash('Login failed! Incorrect email or password.')->error(),
        ]);
    }
}