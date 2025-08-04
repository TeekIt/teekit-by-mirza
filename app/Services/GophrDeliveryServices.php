<?php

namespace App\Services;

use App\Models\OrdersFromOtherSeller;
use App\Orders;
use stdClass;
use Illuminate\Support\Str;

final class GophrDeliveryServices
{
    public static function getApiKey(): string
    {
        return config('gophr.GOPHR_API_KEY');
    }

    public static function getApiUrl(): string
    {
        return (app()->environment('production')) ?
            'https://api.gophr.com/v2-commercial-api' :
            'https://api-sandbox.gophr.com/v2-commercial-api';
    }

    public static function createJob(array $job): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => static::getApiUrl() . '/jobs?XDEBUG_SESSION=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($job),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key:' . static::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function getJobPricing(array $job): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => static::getApiUrl() . '/quotes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($job),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key:' . static::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    public static function getJob(string $jobId): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => static::getApiUrl() . '/jobs/' . $jobId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Api-Key:' . static::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    public static function cancelJob(string $jobId): stdClass
    {
        $formData = [
            "cancelled_reason" => "TEST_ORDER"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => static::getApiUrl() . '/jobs/'. $jobId .'/cancel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($formData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key:' . static::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}
