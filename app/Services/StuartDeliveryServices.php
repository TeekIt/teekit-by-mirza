<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\StuartDelivery;
use App\Orders;
use Illuminate\Support\Facades\Http;
use Throwable;
use Illuminate\Support\Carbon;

final class StuartDeliveryServices
{
    public static function getSandBoxJobsUrl()
    {
        return 'https://api.sandbox.stuart.com/v2/jobs';
    }

    public static function getSandBoxTokenUrl()
    {
        return 'https://api.sandbox.stuart.com/oauth/token';
    }

    public static function getProductionJobsUrl()
    {
        return 'https://api.stuart.com/v2/jobs';
    }

    public static function getProductionTokenUrl()
    {
        return 'https://api.stuart.com/oauth/token';
    }
    /**
     * It will get a fresh token for hitting Stuart delivery API
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartAccessToken()
    {
        $url = (app()->environment('production')) ? self::getProductionTokenUrl() : self::getSandBoxTokenUrl();

        return Http::asForm()->post($url, [
            'client_id' => config('stuart.STUART_CLIENT_ID'),
            'client_secret' => config('stuart.STUART_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ])->json()['access_token'];

        // $stuartAuth = Http::asForm()->post($url, [
        //     'client_id' => env('STUART_CLIENT_ID'),
        //     'client_secret' => env('STUART_CLIENT_SECRET'),
        //     'grant_type' => 'client_credentials',
        //     'scope' => 'api'
        // ]);
        // $stuartAuth = $stuartAuth->json();

        // return $stuartAuth['access_token'];
    }

    public static function stuartSandboxAccessToken()
    {
        $stuartAuth = Http::asForm()->post(self::getSandBoxTokenUrl(), [
            'client_id' => env('STUART_CLIENT_ID'),
            'client_secret' => env('STUART_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuartAuth = $stuartAuth->json();

        return $stuartAuth['access_token'];
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartProductionAccessToken()
    {
        $stuartAuth = Http::asForm()->post(self::getProductionTokenUrl(), [
            'client_id' => env('STUART_PRODUCTION_CLIENT_ID'),
            'client_secret' => env('STUART_PRODUCTION_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuartAuth = $stuartAuth->json();

        return $stuartAuth['access_token'];
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartSandboxJobCreation(string $accessToken, array $job)
    {
        return Http::withToken($accessToken)->post(self::getSandBoxJobsUrl(), $job)->json();
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartProductionJobCreation(string $accessToken, array $job)
    {
        return Http::withToken($accessToken)->post(self::getProductionJobsUrl(), $job)->json();
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartSandboxJobStatus(string $accessToken, $jobId)
    {
        $response = Http::withToken($accessToken)->patch(self::getSandBoxJobsUrl() . '/' . $jobId);

        return $response->json();
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartProductionJobStatus(string $accessToken, $jobId)
    {
        $response = Http::withToken($accessToken)->patch(self::getProductionJobsUrl() . '/' . $jobId);
        
        return $response->json();
    }
    /**
     * Creates a stuart delivery job for a livewire component
     * @author Muhammad Abdullah Mirza
     */
    public static function stuartJobCreationLivewire($orderId, $customOrderId = null)
    {
        try {
            $orderDetails = Orders::getById($orderId);
            $transportType = Orders::fetchTransportType($orderId);
            $accessToken = (app()->environment('production')) ? static::stuartProductionAccessToken() : static::stuartSandboxAccessToken();

            $job = [
                'job' => [
                    'pickup_at' => Carbon::now()->addMinutes(10),
                    'assignment_code' => $orderId,
                    'pickups' => [
                        [
                            'address' => $orderDetails->store->full_address,
                            'comment' => 'Please come at the pickup point as early as possible. Also call us to confirm the order package type.',
                            'contact' => [
                                'firstname' => $orderDetails->store->name,
                                // 'lastname' => 'null',
                                'phone' => $orderDetails->store->business_phone,
                                'email' => $orderDetails->store->email,
                                'company' => $orderDetails->store->business_name
                            ]
                        ]
                    ],
                    'dropoffs' => [
                        [
                            'package_type' => 'medium',
                            'package_description' => 'Package purchased from Teek it.',
                            'transport_type' => $transportType,
                            'client_reference' => ($customOrderId) ? $customOrderId : $orderId,
                            'address' => $orderDetails->address . ' House#' . $orderDetails->house_no,
                            'comment' => 'Please try to call the customer before reaching the destination.',
                            // 'end_customer_time_window_start' => '2021-12-12T11:00:00.000+02:00',
                            // 'end_customer_time_window_end' => '2021-12-12T13:00:00.000+02:00',
                            'contact' => [
                                'firstname' => $orderDetails->receiver_name,
                                // 'lastname' => 'null',
                                'phone' => $orderDetails->phone_number,
                                // 'email' => 'client3@email.com',
                                // 'company' => 'Sample Company Inc.'
                            ]
                        ]
                    ]
                ]
            ];

            $data = (app()->environment('production')) ? static::stuartProductionJobCreation($accessToken, $job) : static::stuartSandboxJobCreation($accessToken, $job);
            if ($data && !isset($data['error'])) {
                StuartDelivery::insertInfo($orderId, $data['id']);

                Orders::updateOrderStatus($orderId, OrderStatusEnum::STUART_DELIVERY);

                return 'JobCreated';
            } else {
                $message = $data['error'] . ': ' . $data['message'];
                if ($data['error'] == 'JOB_DISTANCE_NOT_ALLOWED') $message = $message . " " . $transportType;

                return 'StuartErrorA: ' . $message;
            }
        } catch (Throwable $error) {
            report($error);

            return 'StuartErrorB: ' . $data['error'] . ': ' . $data['message'];
        }
    }
}
