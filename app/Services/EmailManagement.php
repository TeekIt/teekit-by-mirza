<?php

namespace App\Services;

use App\Mail\StoreRegisterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class EmailManagement
{
    public static function sendStoreApprovedEmail(object $user)
    {
        $html = '<html>
                    Hi, ' . $user->name . '<br><br>
                    Thank you for registering on ' . env('APP_NAME') . '.
                    <br>
                    Your store has been approved. Please login to your
                    <a href="' . url('/') . '">Store</a> to manage it.
                    <br><br><br>
                </html>';
        $subject = url('/') . ': Account Approved!';
        Mail::to($user->email)->send(new StoreRegisterMail($html, $subject));
    }

    public static function sendDriverAccVerificationMail(object $driver)
    {
        $verification_code = Crypt::encrypt($driver->email);
            $FRONTEND_URL = url('/');

            $account_verification_link = $FRONTEND_URL . '/auth/verify?token=' . $verification_code;

            $body = '<html>
                Hi, ' . $driver->f_name . '<br><br>
                Thank you for registering on ' . env('APP_NAME') . '.
                <br>
                Here is your account verification link. Click on below link to verify your account. <br><br>
                <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
                ' . $account_verification_link . '
                <br><br><br>
                </html>';

            $subject = env('APP_NAME') . ': Account Verification';

            Mail::to($driver->email)->send(new StoreRegisterMail($body, $subject));
    }
}
