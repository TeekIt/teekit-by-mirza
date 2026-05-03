<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\VanInventoryOrder;
use App\Models\User;

final class OrderServices
{
    public static int $maxDistanceInMiles = 5;

    public static function getTotalWithExtraCharge(float $orderTotalAmount, float $totalWeight): float
    {
        return $orderTotalAmount + ((2.5 + 1.25) * (self::$maxDistanceInMiles + self::getDeliveryFee($totalWeight)));
    }

    public static function getTotalWeight(array|Orders|OrdersFromOtherSeller|VanInventoryOrder $order): float
    {
        if (is_array($order)) {
            $totalWeight = 0.0;

            foreach ($order as $orderItem) {
                $totalWeight += (float) ($orderItem['weight'] * $orderItem['product_qty']);
            }

            return $totalWeight;
        }

        if ($order instanceof Orders || $order instanceof VanInventoryOrder) {
            /* The sum() function will loop over all $orderItems */
            return $order->orderItems->sum(static function ($orderItem) {
                return (float) ($orderItem->product->weight * $orderItem->product_qty);
            });
        }

        return $order->product->weight;
    }

    public static function getTotalHeight(Orders|OrdersFromOtherSeller|VanInventoryOrder $order): float
    {
        if ($order instanceof Orders || $order instanceof VanInventoryOrder) {
            return $order->orderItems->pluck('product')->sum('height');
        }

        return $order->product->height;
    }

    public static function getTotalWidth(Orders|OrdersFromOtherSeller|VanInventoryOrder $order): float
    {
        if ($order instanceof Orders || $order instanceof VanInventoryOrder) {
            return $order->orderItems->pluck('product')->sum('width');
        }

        return $order->product->width;
    }

    public static function getTotalLength(Orders|OrdersFromOtherSeller|VanInventoryOrder $order): float
    {
        if ($order instanceof Orders || $order instanceof VanInventoryOrder) {
            return $order->orderItems->pluck('product')->sum('length');
        }

        return $order->product->length;
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

    public static function getDeliveryFee(float $totalWeight): float
    {
        return match (true) {
            $totalWeight < 5 => 0.15,
            $totalWeight < 10 => 0.50,
            $totalWeight < 15 => 0.75,
            $totalWeight < 20 => 1.50,
            default => 2.0,
        };
    }

    public static function getTotalDeliveryCharges(
        float $sellerLat,
        float $sellerLon,
        float $buyerLat,
        float $buyerLon,
        float $totalWeight,
    ): float {
        $distanceInMiles = GoogleMapServices::getDistanceInMiles($sellerLat, $sellerLon, $buyerLat, $buyerLon);

        // return (2.5 + 1.25) * ($distanceInMiles + static::getDeliveryFee($totalWeight));
        return 2.5 + 1.25 * $distanceInMiles + self::getDeliveryFee($totalWeight);
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
        string $buyerCountryCode,
        string $buyerNumber,
        int $orderId,
        string $verificationCode
    ): void {
        $buyerNumber = $buyerCountryCode . $buyerNumber;
        /* Msg for sending SMS notification of this "New Order" */
        $messageForSeller = 'A new order #' . $orderId . " has been received. Please visit Teek It's seller dashboard:https://app.teekit.co.uk/login";

        $messageForBuyer = 'Thanks for your order! Your order has been delivered to the store. Please quote verification code: ' . $verificationCode . ' on delivery. (TeekIt)';

        /* To restrict "New Order" SMS notifications only for UK numbers */
        if (str_contains($seller->business_phone, '+44')) {
            /* Seller Number */
            TwilioSmsServices::sendSms($seller->business_phone, $messageForSeller);
        }
        /* Buyer Number */
        TwilioSmsServices::sendSms($buyerNumber, $messageForBuyer);
        /* Rameesha Number */
        // TwilioSmsServices::sendSms('+923362451199', $messageForBuyer);
        /* Azim Number */
        TwilioSmsServices::sendSms('+447976621849', $messageForSeller);
        /* Eesa Number */
        // TwilioSmsServices::sendSms('+447490020063', $messageForSeller);
        /* Junaid Number */
        TwilioSmsServices::sendSms('+447817332090', $messageForSeller);
        /* Mirza Number */
        TwilioSmsServices::sendSms('+923170155625', $messageForSeller);
    }
}
