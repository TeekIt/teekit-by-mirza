<?php

namespace App\Services;

use App\Models\OrdersFromOtherSeller;
use App\Orders;
use stdClass;
use Illuminate\Support\Str;
use Stripe\Service\Climate\OrderService;

final class GophrServices
{
    public static function getApiKey(): string
    {
        return env('GOPHR_API_KEY');
    }

    public static function getApiUrl(): string
    {
        return (app()->environment('production')) ?
            'https://api.gophr.com/v2-commercial-api' :
            'https://api-sandbox.gophr.com/v2-commercial-api';
    }

    public static function generateUuid(): string
    {
        return Str::uuid();
    }


    public static function createJob(
        Orders|OrdersFromOtherSeller $order,
        string $parcelDescription
    ): stdClass {
        $parcelData = [
            "parcel_external_id" => static::generateUuid(),
            "parcel_reference_number" => static::generateUuid(),
            "parcel_description" => $parcelDescription,
            "width" => OrderServices::getTotalWidth($order),
            "length" => OrderServices::getTotalLength($order),
            "height" => OrderServices::getTotalHeight($order),
            "weight" => OrderServices::getTotalWeight($order),
        ];

        $formData = [
            "is_confirmed" => 1,
            "external_id" => static::generateUuid(),
            "pickups" => [
                [
                    "pickup_address1" => $order->seller->full_address,
                    "pickup_city" => $order->seller->city,
                    "pickup_postcode" => $order->seller->postcode,
                    "pickup_country_code" => "GB",
                    "pickup_location_lat" => $order->seller->lat,
                    "pickup_location_lng" => $order->seller->lon,
                    "pickup_person_name" => $order->seller->name,
                    "pickup_mobile_number" => $order->seller->business_phone,
                    "parcels" => [
                        $parcelData
                    ]
                ]
            ],
            "dropoffs" => [
                [
                    "dropoff_address1" => $order->address,
                    "dropoff_city" => $order->city,
                    "dropoff_postcode" => $order->postcode,
                    "dropoff_country_code" => "GB",
                    "dropoff_location_lat" => $order->customer_lat,
                    "dropoff_location_lng" => $order->customer_lon,
                    "dropoff_person_name" => $order->customer_name,
                    "dropoff_mobile_number" => $order->phone_number,
                    "dropoff_deadline" => DeliveryServices::getStandardDeliveryDeadline()->toIso8601String(),
                    "parcels" => [
                        $parcelData
                    ]
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => static::getApiUrl() . '/jobs?XDEBUG_SESSION=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($formData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key:' . static::getApiKey(),
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    public static function cancelJob() {}
}
