<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Mail\BuyerVerificationMail;
use App\Mail\NewSellerRegistrationMail;
use App\Mail\OrderIsCanceledMail;
use App\Mail\OrderIsReadyForPickupMail;
use App\Mail\ProductByBuyerOrderDetailsToNearBySellersMail;
use App\Mail\RegeneratedStripeConnectAccMail;
use App\Mail\SellerApprovedMail;
use App\Mail\StripeConnectAccMail;
use App\Mail\VanInventoryOrderMail;
use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class EmailServices
{
    public static function getVerificationLink($verificationCode)
    {
        return url('/') . '/auth/verify?token=' . $verificationCode;
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

    public static function sendProductByBuyerOrderDetailsToNearBySellersMail(array $nearBySellersEmails, Orders $order)
    {
        Mail::to('azim@teekit.co.uk')->bcc($nearBySellersEmails)->send(new ProductByBuyerOrderDetailsToNearBySellersMail($order));
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

        Mail::to([config('constants.ADMIN_EMAIL'), 'mirzaabdullahizhar.teekit@gmail.com'])->send(
            new NewSellerRegistrationMail($user, $sellerType, $accountVerificationLink, $parentSeller)
        );
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

    public static function sendVanInventoryOrderMail(
        string $sellerEmail,
        string $sellerName,
        array $orderItems,
        string $vanLocation
    ): void {
        Mail::to($sellerEmail)->send(new VanInventoryOrderMail($sellerName, $orderItems, $vanLocation));
    }
}
