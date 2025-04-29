<?php

namespace App\Services;

use App\Models\OrdersFromOtherSeller;
use App\Orders;
use stdClass;
use Illuminate\Support\Str;
use Stripe\Service\Climate\OrderService;

final class UberDeliveryServices
{
    
    // protected $baseUrl;
    // protected $serverToken;
    // protected $uberService;



    public static function getApiUrl(): string
    {
        return (app()->environment('production')) ?
            'https://api.uber.com/v1/deliveries/' :
            'https://sandbox-login.uber.com/oauth/v2/token';
    }

    public static function generateUuid(): string
    {
        return Str::uuid();
    }

    public static function createJob(Orders|OrdersFromOtherSeller $order)
    {
        

        $customerId = static::generateUuid(); // Replace with your customer ID
        $token = '{token}'; // Replace with your Bearer token

        $url = "https://api.uber.com/v1/customers/{$customerId}/deliveries";

        $data = [
                "pickup_name" => $order->seller->name,
                "pickup_address" => json_encode([
                "street_address" => $order->seller->full_address,
                "city" => $order->seller->city,
                "state" => $order->seller->state,
                "zip_code" => $order->seller->postcode,
                "country" => "GB"
                ]),
                "pickup_phone_number" => $order->seller->business_phone,
                "dropoff_name" => $order->customer_name,
                "dropoff_address" => json_encode([
                "street_address" => $order->address, 
                "city" => $order->city,
                "state" => $order->state,
                "zip_code" => $order->postcode,
                "country" => "GB"
                ]),
                "dropoff_phone_number" => $order->phone_number,
                "manifest_items" => [
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
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            echo "HTTP status code: $httpCode\n";
            echo "Response: $response\n";
        }

        curl_close($ch);

        return json_decode($response);

        
    }
   

    public static function getJob(string $deliveryId)
    {
        $customerId = 'your_customer_id';
        // $deliveryId = 'your_delivery_id';
        $token = 'your_token';

        $url = "https://api.uber.com/v1/customers/{$customerId}/deliveries/{$deliveryId}";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        } else {
            $decoded = json_decode($response, true);
            print_r($decoded);
        }

        curl_close($curl);
        return json_decode($response);
    }

    public static function cancelJob(UberDeliveryServices $uberService,string $deliveryId)
    {
        $customerId = 'your_customer_id';
        // $deliveryId = 'your_delivery_id';
        $token = 'your_token';

        $url = "https://api.uber.com/v1/customers/{$customerId}/deliveries/{$deliveryId}/cancel";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        } else {
            $decoded = json_decode($response, true);
            print_r($decoded);
        }

        curl_close($curl);
        return json_decode($response);

        }
   
}
