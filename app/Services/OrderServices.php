<?php

namespace App\Services;

final class OrderServices
{
    public static function getDriverCharges(
        $sellerLat,
        $sellerLon,
        $customerLat,
        $customerLon,
        float $totalWeight,
        float $totalVolumn,
    ): float {
        // $store_lat = $seller->lat;
        // $store_lon = $seller->lon;
        // $customer_lat = $request->lat;
        // $customer_lon = $request->lon;
        // $distance = $this->calculateDistance($customer_lat, $customer_lon, $store_lat, $store_lon);
        $distance = GoogleMapServices::getDistanceInMiles($sellerLat, $sellerLon, $customerLat, $customerLon);

        return DriverFairServices::calculateDriverFair2($totalWeight, $totalVolumn, $distance);
    }
}
