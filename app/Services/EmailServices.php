<?php

namespace App\Services;

use App\Drivers;
use App\Mail\BuyerVerificationMail;
use App\Mail\NewSellerRegistrationMail;
use App\Mail\OrderIsCanceledMail;
use App\Mail\OrderIsReadyForPickupMail;
use App\Mail\SellerApprovedMail;
use App\Mail\StoreRegisterMail;
use App\Models\OrdersFromOtherSeller;
use App\Orders;
use App\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class EmailServices
{
    public static function getVerificationLink($verificationCode)
    {
        return url('/') . '/auth/verify?token=' . $verificationCode;
    }

    public static function sendBuyerAccVerificationMail(User $user)
    {
        $verificationCode = Crypt::encrypt($user->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);

        Mail::to($user->email)->send(new BuyerVerificationMail($user, $accountVerificationLink));
    }

    public static function sendNewChildStoreMail(User $user, $parent_store)
    {
        $verificationCode = Crypt::encrypt($user->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);

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
            <a href="' . $accountVerificationLink . '">Verify</a> OR Copy This in your Browser
            ' . $accountVerificationLink . '
            <br><br><br>
        </html>';

        $subject = env('APP_NAME') . ': Child Store Account Verification Required';

        Mail::to(config('constants.ADMIN_EMAIL'))->send(new StoreRegisterMail($html, $subject));
        Mail::to('mirzaabdullahizhar.teekit@gmail.com')->send(new StoreRegisterMail($html, $subject));
    }

    public static function sendNewSellerMail(User $user, string $sellerType)
    {
        $verificationCode = Crypt::encrypt($user->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);

        // $html = '<html>
        //     Hi! Team Teek IT.<br><br>
        //    A new store signed up today.
        //     <br>
        //    Please verify their details and take your decision to allow or disallow the store on our platform.<br><br>
        //    <strong>Store Name:</strong> '  .  $user->business_name   .  '<br>
        //    <strong>Owner Name:</strong> '  .  $user->name   .  '<br>
        //    <strong>Email:</strong> '  .  $user->email  .  '<br>
        //    <strong>Contact:</strong> '  .  $user->business_phone  .  '<br>
        //    <strong>Address:</strong> '  .  $user->address_1  .  '
        //    <br><br>
        //     <a href="' . $accountVerificationLink . '">Verify</a> OR Copy This in your Browser
        //     ' . $accountVerificationLink . '
        //     <br><br><br>
        // </html>';

        // Mail::to(config('constants.ADMIN_EMAIL'))->send(new StoreRegisterMail($html, $subject));
        Mail::to(config('constants.ADMIN_EMAIL'))->send(
            new NewSellerRegistrationMail($user, 'Parent', $accountVerificationLink)
        );
        // Mail::to('mirzaabdullahizhar.teekit@gmail.com')->send(new StoreRegisterMail($html, $subject));
    }

    public static function sendDriverAccVerificationMail(Drivers $driver)
    {
        $verificationCode = Crypt::encrypt($driver->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);

        $body = '<html>
                Hi, ' . $driver->f_name . '<br><br>
                Thank you for registering on ' . env('APP_NAME') . '.
                <br>
                Here is your account verification link. Click on below link to verify your account. <br><br>
                <a href="' . $accountVerificationLink . '">Verify</a> OR Copy This in your Browser
                ' . $accountVerificationLink . '
                <br><br><br>
                </html>';

        $subject = env('APP_NAME') . ': Account Verification';

        Mail::to($driver->email)->send(new StoreRegisterMail($body, $subject));
    }

    public static function sendSellerApprovedMail(User $user)
    {
        Mail::to($user->email)->send(new SellerApprovedMail($user));
    }

    public static function sendPickupYourOrderMail(Orders $order)
    {
        Mail::to($order->buyer->email)->send(new OrderIsReadyForPickupMail($order, $order->seller));
    }

    public static function sendPickupYourOrderFromOtherSellerMail(OrdersFromOtherSeller $ordersFromOtherSeller)
    {
        Mail::to($ordersFromOtherSeller->buyer->email)->send(
            new OrderIsReadyForPickupMail($ordersFromOtherSeller, $ordersFromOtherSeller->seller)
        );
    }

    public static function sendOrderHasBeenCancelledMail(Orders|OrdersFromOtherSeller $order)
    {
        Mail::to([$order->buyer->email])->send(new OrderIsCanceledMail($order));
    }
}
