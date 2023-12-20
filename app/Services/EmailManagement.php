<?php

namespace App\Services;

use App\Drivers;
use App\Mail\OrderIsCanceledMail;
use App\Mail\OrderIsReadyMail;
use App\Mail\StoreRegisterMail;
use App\Orders;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class EmailManagement
{
    public static function getVerificationLink($verification_code)
    {
        return url('/') . '/auth/verify?token=' . $verification_code;
    }

    public static function sendNewChildStoreMail(User $user, $parent_store)
    {
        $verification_code = Crypt::encrypt($user->email);
        $account_verification_link = self::getVerificationLink($verification_code);

        $html = '<html>
            Hi! Team Teek IT.<br><br>
            ' .  $parent_store  . ' child store has signed up today.
            <br>
           Please verify their details and take your decision to allow or disallow the store on our platform.<br><br>
           <strong>Store Name:</strong> '  .  $user->business_name   .  '<br>
           <strong>Owner Name:</strong> '  .  $user->name   .  '<br>
           <strong>Email:</strong> '  .  $user->email  .  '<br>
           <strong>Parent Store:</strong> '  .  $parent_store  .  '<br>
           <strong>Contact:</strong> '  .  $user->business_phone  .  '<br>
           <strong>Address:</strong> '  .  $user->address_1  .  '
           <br><br>
            <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
            ' . $account_verification_link . '
            <br><br><br>
        </html>';

        $subject = env('APP_NAME') . ': Child Store Account Verification Required';

        Mail::to(config('constants.ADMIN_EMAIL'))->send(new StoreRegisterMail($html, $subject));
        Mail::to('mirzaabdullahizhar.teekit@gmail.com')->send(new StoreRegisterMail($html, $subject));
    }

    public static function sendNewParentStoreMail(User $user)
    {
        $verification_code = Crypt::encrypt($user->email);
        $account_verification_link = self::getVerificationLink($verification_code);

        $html = '<html>
            Hi! Team Teek IT.<br><br>
           A new store signed up today.
            <br>
           Please verify their details and take your decision to allow or disallow the store on our platform.<br><br>
           <strong>Store Name:</strong> '  .  $user->business_name   .  '<br>
           <strong>Owner Name:</strong> '  .  $user->name   .  '<br>
           <strong>Email:</strong> '  .  $user->email  .  '<br>
           <strong>Contact:</strong> '  .  $user->business_phone  .  '<br>
           <strong>Address:</strong> '  .  $user->address_1  .  '
           <br><br>
            <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
            ' . $account_verification_link . '
            <br><br><br>
        </html>';

        $subject = env('APP_NAME') . ': Parent Store Account Verification Required';

        Mail::to(config('constants.ADMIN_EMAIL'))->send(new StoreRegisterMail($html, $subject));
        Mail::to('mirzaabdullahizhar.teekit@gmail.com')->send(new StoreRegisterMail($html, $subject));
    }

    public static function sendDriverAccVerificationMail(Drivers $driver)
    {
        $verification_code = Crypt::encrypt($driver->email);
        $account_verification_link = self::getVerificationLink($verification_code);

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

    public static function sendStoreApprovedMail(User $user)
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

    public static function sendPickupYourOrderMail(Orders $order)
    {
        Mail::to($order->user->email)->send(new OrderIsReadyMail($order));
    }

    public static function sendOrderHasBeenCancelledMail(Orders $order)
    {
        Mail::to([$order->user->email])->send(new OrderIsCanceledMail($order));
    }
}
