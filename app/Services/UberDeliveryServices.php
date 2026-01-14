<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use Illuminate\Support\Str;
use stdClass;

final class UberDeliveryServices
{
    public static function getApiUrl(): string
    {
        return 'https://api.uber.com/v1';
    }

    public static function generateUuid(): string
    {
        return Str::uuid();
    }

    public static function createJob(Orders|OrdersFromOtherSeller $order): stdClass
    {
        $customerId = self::generateUuid(); // Replace with your customer ID
        $token = '{token}'; // Replace with your Bearer token

        $url = self::getApiUrl()."/v1/customers/{$customerId}/deliveries";

        $data = [
            'pickup_name' => $order->seller->name,
            'pickup_address' => json_encode([
                'street_address' => $order->seller->full_address,
                'city' => $order->seller->city,
                'state' => $order->seller->state,
                'zip_code' => $order->seller->postcode,
                'country' => 'GB',
            ]),
            'pickup_phone_number' => $order->seller->business_phone,
            'dropoff_name' => $order->customer_name,
            'dropoff_address' => json_encode([
                'street_address' => $order->address,
                'city' => $order->city,
                'state' => $order->state,
                'zip_code' => $order->postcode,
                'country' => 'GB',
            ]),
            'dropoff_phone_number' => $order->phone_number,
            'manifest_items' => [
                [
                    // "name" => "Bow tie",
                    // "quantity" => 1,
                    // "size" => "small",
                    // "dimensions" => [
                    //     "length" => OrderServices::getTotalLength($order),
                    //     "height" => OrderServices::getTotalHeight($order),
                    //     "depth" => 20,
                    // ],
                    // "price" => 100,
                    // "weight" => 300,
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$token}",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response);
    }

    public static function getJob(string $deliveryId): stdClass
    {
        $customerId = 'your_customer_id';
        // $deliveryId = 'your_delivery_id';
        $token = 'your_token';

        $url = self::getApiUrl()."/v1/customers/{$customerId}/deliveries/{$deliveryId}";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$token,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    public static function cancelJob(string $deliveryId): stdClass
    {
        $customerId = 'your_customer_id';
        // $deliveryId = 'your_delivery_id';
        $token = 'your_token';

        $url = self::getApiUrl()."/v1/customers/{$customerId}/deliveries/{$deliveryId}/cancel";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$token,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}
