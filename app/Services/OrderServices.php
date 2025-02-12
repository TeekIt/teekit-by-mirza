<?php

namespace App\Services;

use App\Models\OrdersFromOtherSeller;
use App\Orders;
use App\User;

final class OrderServices
{
    public static function getTotalWithExtraCharge(float $totalAmout): float
    {
        return $totalAmout + config('constants.EXTRA_CHARGE_AMOUNT');
    }

    public static function getTotalWeight(array|Orders|OrdersFromOtherSeller $order): float
    {
        if (is_array($order)) {
            return array_sum(array_column($order, 'weight'));
        }
        
        return $order->order_items->pluck('product')->sum('weight');
    }

    public static function getTotalHeight(Orders|OrdersFromOtherSeller $order): float
    {
        return $order->order_items->pluck('product')->sum('height');
    }

    public static function getTotalWidth(Orders|OrdersFromOtherSeller $order): float
    {
        return $order->order_items->pluck('product')->sum('width');
    }

    public static function getTotalLength(Orders|OrdersFromOtherSeller $order): float
    {
        return $order->order_items->pluck('product')->sum('length');
    }

    public static function getTotalOfGivenVolume(array $order): float
    {
        return array_sum(array_column($order, 'volumn'));
    }

    public static function getTotalItems(array $order): float
    {
        return array_sum(array_column($order, 'product_qty'));
    }

    public static function getOrderTotal(array $orderItems): float
    {
        $orderTotal = 0.00;

        foreach ($orderItems as $singleIndex) {
            $orderTotal += $singleIndex['product_price'] * $singleIndex['product_qty'];
        }

        return $orderTotal;
    }

    public static function getDeliveryCharges(
        float $sellerLat,
        float $sellerLon,
        float $buyerLat,
        float $buyerLon,
        float $totalWeight,
    ): float {
        if ($totalWeight < 5) {
            $deliveryFee = 0.15;
        } else if ($totalWeight >= 5 && $totalWeight < 10) {
            $deliveryFee = 0.50;
        } else if ($totalWeight >= 10 && $totalWeight < 15) {
            $deliveryFee = 0.75;
        } else if ($totalWeight >= 15 && $totalWeight < 20) {
            $deliveryFee = 1.50;
        } else if ($totalWeight >= 20) {
            $deliveryFee = 2.0;
        }

        $distanceInMiles = GoogleMapServices::getDistanceInMiles($sellerLat, $sellerLon, $buyerLat, $buyerLon);

        return (2.5 + 1.25) * ($distanceInMiles + $deliveryFee);
    }

    public static function getDriverCharges(
        $sellerLat,
        $sellerLon,
        $buyerLat,
        $buyerLon,
        float $totalWeight,
        float $totalVolumn,
    ): float {
        $distance = GoogleMapServices::getDistanceInMiles($sellerLat, $sellerLon, $buyerLat, $buyerLon);

        return DriverFairServices::calculateDriverFair2($totalWeight, $totalVolumn, $distance);
    }

    public static function sendBulkSms(
        User $seller,
        string $buyerNumber,
        int $orderId,
        string $verificationCode
    ): void {
        /* Msg for sending SMS notification of this "New Order" */
        $messageForAdmin = "A new order #" . $orderId . " has been received. 
        Please check Teek It's seller dashboard, or SignIn here now:https://app.teekit.co.uk/login";

        $messageForBuyer = "Thanks for your order! 
        Your order has been accepted by the store. 
        Please quote verification code: " . $verificationCode . " on delivery. 
        TeekIt";

        /* To restrict "New Order" SMS notifications only for UK numbers */
        if (str_contains($seller->business_phone, '+44')) {
            /* Seller Number */
            TwilioSmsService::sendSms($seller->business_phone, $messageForAdmin);
        }
        /* Customer Number */
        TwilioSmsService::sendSms($buyerNumber, $messageForBuyer);
        /* Rameesha Number */
        TwilioSmsService::sendSms('+923362451199', $messageForBuyer);
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
