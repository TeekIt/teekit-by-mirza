<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Mail\BuyerVerificationMail;
use App\Mail\CustomProductOrderDetailsToNearBySellersMail;
use App\Mail\NewSellerRegistrationMail;
use App\Mail\OrderIsCanceledMail;
use App\Mail\OrderIsReadyForPickupMail;
use App\Mail\RegeneratedStripeConnectAccMail;
use App\Mail\SellerApprovedMail;
use App\Mail\StoreRegisterMail;
use App\Mail\StripeConnectAccMail;
use App\Models\Driver;
use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class EmailServices
{
    public static function getVerificationLink($verificationCode)
    {
        return url('/').'/auth/verify?token='.$verificationCode;
    }

    public static function sendRegeneratedStripeConnectAccMail(User $user)
    {
        $response = StripeServices::getConnectAccountLink($user);

        Mail::to($user->email)->send(new RegeneratedStripeConnectAccMail($response->url));
    }

    public static function sendStripeConnectAccMail(User $user)
    {
        $response = StripeServices::getConnectAccountLink($user);

        Mail::to($user->email)->send(new StripeConnectAccMail($user, $response->url));
    }

    public static function sendCustomProductOrderDetailsToNearBySellersMail(array $nearBySellersEmails, Orders $order)
    {
        Mail::to('azim@teekit.co.uk')->bcc($nearBySellersEmails)->send(new CustomProductOrderDetailsToNearBySellersMail($order));
    }

    public static function sendBuyerAccVerificationMail(User $user)
    {
        $verificationCode = Crypt::encrypt($user->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);

        Mail::to($user->email)->send(new BuyerVerificationMail($user, $accountVerificationLink));
    }

    public static function sendNewSellerMail(User $user, UserRoleEnum $sellerType, ?string $parentSeller = null)
    {
        $verificationCode = Crypt::encrypt($user->email);
        $accountVerificationLink = self::getVerificationLink($verificationCode);
        
        // Mail::raw('This is a plain text notification for the new seller.', function ($message) {
        //     $message->to([config('constants.ADMIN_EMAIL'), 'mirzaabdullahizhar.teekit@gmail.com'])
        //             ->subject('New Seller Registration');
        // });

        Mail::to([config('constants.ADMIN_EMAIL'), 'mirzaabdullahizhar.teekit@gmail.com'])->send(
            new NewSellerRegistrationMail($user, $sellerType, $accountVerificationLink, $parentSeller)
        );
    }

    public static function sendDriverAccVerificationMail(Driver $driver)
    {
        // $verificationCode = Crypt::encrypt($driver->email);
        // $accountVerificationLink = self::getVerificationLink($verificationCode);

        // $body = '<html>
        //         Hi, ' . $driver->f_name . '<br><br>
        //         Thank you for registering on ' . config('app.name') . '.
        //         <br>
        //         Here is your account verification link. Click on below link to verify your account. <br><br>
        //         <a href="' . $accountVerificationLink . '">Verify</a> OR Copy This in your Browser
        //         ' . $accountVerificationLink . '
        //         <br><br><br>
        //         </html>';

        // $subject = config('app.name') . ': Account Verification';

        // Mail::to($driver->email)->send(new StoreRegisterMail($body, $subject));
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
