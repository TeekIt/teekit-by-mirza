<?php

namespace App\Services;

use App\User;

final class OrderServices
{
    public static function getTotalWeight(array $order): float
    {
        return array_sum(array_column($order, 'weight'));
    }

    public static function getTotalVolumn(array $order): float
    {
        return array_sum(array_column($order, 'volumn'));
    }

    public static function getTotalItems(array $order): float
    {
        return array_sum(array_column($order, 'qty'));
    }

    public static function getOrderTotal(array $order): float
    {
        $orderTotal = 0.00;

        foreach ($order as $orderItem) {
            $orderTotal += $orderItem['price'] * $orderItem['qty'];
        }

        return $orderTotal;
    }

    public static function getDriverCharges(
        $sellerLat,
        $sellerLon,
        $customerLat,
        $customerLon,
        float $totalWeight,
        float $totalVolumn,
    ): float {
        // $distance = $this->calculateDistance($customer_lat, $customer_lon, $store_lat, $store_lon);
        $distance = GoogleMapServices::getDistanceInMiles($sellerLat, $sellerLon, $customerLat, $customerLon);

        return DriverFairServices::calculateDriverFair2($totalWeight, $totalVolumn, $distance);
    }

    public static function sendBulkSms(
        User $seller,
        string $customerNumber,
        int $orderId,
        string $verificationCode
    ): void {
        /* Msg for sending SMS notification of this "New Order" */
        $messageForAdmin = "A new order #" . $orderId . " has been received. 
        Please check Teek It's seller dashboard, or SignIn here now:https://app.teekit.co.uk/login";

        $messageForCustomer = "Thanks for your order! 
        Your order has been accepted by the store. 
        Please quote verification code: " . $verificationCode . " on delivery. 
        TeekIt";

        /* To restrict "New Order" SMS notifications only for UK numbers */
        if (str_contains($seller->business_phone, '+44')) {
            /* Seller Number */
            TwilioSmsService::sendSms($seller->business_phone, $messageForAdmin);
        }
        /* Customer Number */
        TwilioSmsService::sendSms($customerNumber, $messageForCustomer);
        /* Rameesha Number */
        TwilioSmsService::sendSms('+923362451199', $messageForCustomer);
        /* Azim Number */
        TwilioSmsService::sendSms('+447976621849', $messageForAdmin);
        /* Eesa Number */
        TwilioSmsService::sendSms('+447490020063', $messageForAdmin);
        /* Junaid Number */
        TwilioSmsService::sendSms('+447817332090', $messageForAdmin);
        /* Mirza Number */
        TwilioSmsService::sendSms('+923170155625', $messageForAdmin);
    }
}
