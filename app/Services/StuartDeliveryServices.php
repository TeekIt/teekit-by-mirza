<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\PackageWeightEnum;
use App\Enums\StuartPackageTypeEnum;
use App\Models\Orders;
use App\Models\StuartDelivery;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

final class StuartDeliveryServices
{
    public static function prepareJobArray(
        string $pickupAt,
        string $assignmentCode,
        string $pickupAddress,
        string $senderName,
        string $senderPhone,
        string $senderEmail,
        string $packageType,
        string $dropoffAddress,
        string $unitAddress,
        string $receiverName,
        string $receiverPhone,
        string $receiverEmail
    ): array {
        return [
            'job' => [
                'pickup_at' => $pickupAt,
                'assignment_code' => $assignmentCode,
                'pickups' => [
                    [
                        'address' => $pickupAddress,
                        'contact' => [
                            'firstname' => $senderName,
                            'phone' => $senderPhone,
                            'email' => $senderEmail,
                        ],
                    ],
                ],
                'dropoffs' => [
                    [
                        'package_type' => $packageType,
                        'client_reference' => $assignmentCode,
                        'address' => $dropoffAddress,
                        'comment' => $unitAddress,
                        'contact' => [
                            'firstname' => $receiverName,
                            'phone' => $receiverPhone,
                            'email' => $receiverEmail,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function getJobsUrl(): string
    {
        return (app()->environment('production')) ? 'https://api.stuart.com/v2/jobs' : 'https://api.sandbox.stuart.com/v2/jobs';
    }

    public static function getJobPricingUrl(): string
    {
        return (app()->environment('production'))
            ? 'https://api.stuart.com/v2/jobs/pricing' :
            'https://api.sandbox.stuart.com/v2/jobs/pricing';
    }

    public static function getTokenUrl(): string
    {
        return (app()->environment('production')) ? 'https://api.stuart.com/oauth/token' : 'https://api.sandbox.stuart.com/oauth/token';
    }

    /**
     * It will get a fresh token for hitting Stuart delivery APIs
     *
     * @author Muhammad Abdullah Mirza
     */
    public static function getAccessToken(): string
    {
        $response = Http::asForm()->post(self::getTokenUrl(), [
            'client_id' => config('stuart.STUART_CLIENT_ID'),
            'client_secret' => config('stuart.STUART_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api',
        ])->json();

        if (isset($response['error'])) {
            throw new Exception($response['error_description']);
        }

        return $response['access_token'];
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function getJobPricing(array $job): array
    {
        $response = Http::withToken(self::getAccessToken())->post(self::getJobPricingUrl(), $job)->json();

        if (isset($response['error'])) {
            throw new Exception($response['message']);
        }

        return $response;
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function getJob(string $jobId): array
    {
        $response = Http::withToken(self::getAccessToken())->get(self::getJobsUrl().'/'.$jobId)->json();

        if (isset($response['error'])) {
            throw new Exception($response['message']);
        }

        return $response;
    }

    public static function mapPkgWeightWithStuartPkgType(PackageWeightEnum $packageWeight): string
    {
        switch ($packageWeight) {
            case PackageWeightEnum::SMALL:
                return StuartPackageTypeEnum::SMALL->value;
            case PackageWeightEnum::MEDIUM:
                return StuartPackageTypeEnum::MEDIUM->value;
            case PackageWeightEnum::LARGE:
                return StuartPackageTypeEnum::LARGE->value;
            case PackageWeightEnum::EXTRA_LARGE:
                return StuartPackageTypeEnum::EXTRA_LARGE->value;
            default:
                throw new Exception('Invalid package weight provided');
        }
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function createJob(array $job): array
    {
        $response = Http::withToken(self::getAccessToken())->post(self::getJobsUrl(), $job)->json();

        if (isset($response['error'])) {
            throw new Exception($response['message']);
        }

        return $response;
    }

    /**
     * Creates a stuart delivery job for a livewire component
     *
     * @author Muhammad Abdullah Mirza
     */
    public static function createJobForLivewire($orderId, $customOrderId = null)
    {
        try {
            $orderDetails = Orders::getById($orderId);
            $transportType = Orders::fetchTransportType($orderId);

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
                                'company' => $orderDetails->store->business_name,
                            ],
                        ],
                    ],
                    'dropoffs' => [
                        [
                            'package_type' => 'medium',
                            'package_description' => 'Package purchased from Teek it.',
                            'transport_type' => $transportType,
                            'client_reference' => ($customOrderId) ? $customOrderId : $orderId,
                            'address' => $orderDetails->address.' House#'.$orderDetails->house_no,
                            'comment' => 'Please try to call the customer before reaching the destination.',
                            // 'end_customer_time_window_start' => '2021-12-12T11:00:00.000+02:00',
                            // 'end_customer_time_window_end' => '2021-12-12T13:00:00.000+02:00',
                            'contact' => [
                                'firstname' => $orderDetails->receiver_name,
                                // 'lastname' => 'null',
                                'phone' => $orderDetails->phone_number,
                                // 'email' => 'client3@email.com',
                                // 'company' => 'Sample Company Inc.'
                            ],
                        ],
                    ],
                ],
            ];

            $data = self::createJob($job);
            if ($data && ! isset($data['error'])) {
                StuartDelivery::insertInfo($orderId, $data['id']);

                Orders::updateOrderStatus($orderId, OrderStatusEnum::STUART_DELIVERY);

                return 'JobCreated';
            } else {
                $message = $data['error'].': '.$data['message'];
                if ($data['error'] == 'JOB_DISTANCE_NOT_ALLOWED') {
                    $message = $message.' '.$transportType;
                }

                return 'StuartErrorA: '.$message;
            }
        } catch (Throwable $error) {
            report($error);

            return 'StuartErrorB: '.$data['error'].': '.$data['message'];
        }
    }
}
