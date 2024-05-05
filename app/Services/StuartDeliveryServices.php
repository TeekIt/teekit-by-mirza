<?php

namespace App\Services;

use App\Models\StuartDelivery;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Throwable;
use App\Services\JsonResponseServices;
use Illuminate\Support\Carbon;

final class StuartDeliveryServices {
    /**
     * It will get a fresh token for hitting Stuart delivery API
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public static function stuartSandboxAccessToken()
    {
        $stuart_auth = Http::asForm()->post('' . config("constants.STUART_SANDBOX_TOKEN_URL") . '', [
            'client_id' => config('constants.STUART_SANDBOX_CLIENT_ID'),
            'client_secret' => config('constants.STUART_SANDBOX_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuart_auth = $stuart_auth->json();
        return $stuart_auth['access_token'];
    }
    /**
     * @version 1.0.0
     */
    public static function stuartProductionAccessToken()
    {
        $stuart_auth = Http::asForm()->post('' . config("constants.STUART_PRODUCTION_TOKEN_URL") . '', [
            'client_id' => config('app.STUART_PRODUCTION_CLIENT_ID'),
            'client_secret' => config('app.STUART_PRODUCTION_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuart_auth = $stuart_auth->json();
        return $stuart_auth['access_token'];
    }
    /**
     * @version 1.0.0
     */
    public static function stuartSandboxJobCreation(string $access_token, array $job)
    {
        $response = Http::withToken($access_token)->post('' . config("constants.STUART_SANDBOX_JOBS_URL") . '', $job);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public static function stuartProductionJobCreation(string $access_token, array $job)
    {
        $response = Http::withToken($access_token)->post('' . config("constants.STUART_PRODUCTION_JOBS_URL") . '', $job);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public static function stuartSandboxJobStatus(string $access_token, array $job_id)
    {
        $response = Http::withToken($access_token)->patch('' . config("constants.STUART_SANDBOX_JOBS_URL") . '/' . $job_id);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public static function stuartProductionJobStatus(string $access_token, array $job_id)
    {
        $response = Http::withToken($access_token)->patch('' . config("constants.STUART_PRODUCTION_JOBS_URL") . '/' . $job_id);
        return $response->json();
    }
    /**
     * Creates a stuart delivery job for a livewire component
     * @author Mirza Abdullah Izhar
     */
    public static function stuartJobCreationLivewire($order_id, $custom_order_id = null)
    {
        try {
            $order_details = Orders::getOrderById($order_id);
            $transport_type = Orders::fetchTransportType($order_id);
            $access_token = (url('/') == 'https://app.teekit.co.uk') ? static::stuartProductionAccessToken() : static::stuartSandboxAccessToken();

            $job = [
                'job' => [
                    'pickup_at' => Carbon::now()->addMinutes(10),
                    'assignment_code' => $order_id,
                    'pickups' => [
                        [
                            'address' => $order_details->store->address_1,
                            'comment' => 'Please come at the pickup point as early as possible. Also call us to confirm the order package type.',
                            'contact' => [
                                'firstname' => $order_details->store->name,
                                // 'lastname' => 'null',
                                'phone' => $order_details->store->business_phone,
                                'email' => $order_details->store->email,
                                'company' => $order_details->store->business_name
                            ]
                        ]
                    ],
                    'dropoffs' => [
                        [
                            'package_type' => 'medium',
                            'package_description' => 'Package purchased from Teek it.',
                            'transport_type' => $transport_type,
                            'client_reference' => ($custom_order_id) ? $custom_order_id : $order_id,
                            'address' => $order_details->address . ' House#' . $order_details->house_no,
                            'comment' => 'Please try to call the customer before reaching the destination.',
                            // 'end_customer_time_window_start' => '2021-12-12T11:00:00.000+02:00',
                            // 'end_customer_time_window_end' => '2021-12-12T13:00:00.000+02:00',
                            'contact' => [
                                'firstname' => $order_details->receiver_name,
                                // 'lastname' => 'null',
                                'phone' => $order_details->phone_number,
                                // 'email' => 'client3@email.com',
                                // 'company' => 'Sample Company Inc.'
                            ]
                        ]
                    ]
                ]
            ];

            $data = (url('/') == 'https://app.teekit.co.uk') ? static::stuartProductionJobCreation($access_token, $job) : static::stuartSandboxJobCreation($access_token, $job);
            if ($data && !isset($data['error'])) {
                StuartDelivery::insertInfo($order_id, $data['id']);
                Orders::updateOrderStatus($order_id, 'stuartDelivery');
                return 'JobCreated';
            } else {
                $message = $data['message'];
                if ($data['error'] == 'JOB_DISTANCE_NOT_ALLOWED') $message = $message . " " . $transport_type;
                // JsonResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $message);
                return $message;
            }
        } catch (Throwable $error) {
            report($error);
            // JsonResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $data['message']);
            return $data['message'];
        }
    }
}