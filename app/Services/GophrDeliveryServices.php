<?php

namespace App\Services;

use App\Enums\GophrCancellationReasonEnum;
use Exception;
use stdClass;

final class GophrDeliveryServices
{
    public static function prepareJobArray(
        string $externalId,
        string $pickupAddress,
        string $pickupCity,
        string $pickupPostcode,
        string $pickupLat,
        string $pickupLon,
        string $pickupPersonName,
        string $pickupMobileNumber,
        string $parcelExternalId,
        string $parcelReferenceNumber,
        string $parcelDescription,
        float $width,
        float $length,
        float $height,
        float $weight,
        string $dropoffAddress,
        string $dropoffCity,
        string $dropoffPostcode,
        string $dropoffLat,
        string $dropoffLon,
        string $dropoffPersonName,
        string $dropoffEmail,
        string $dropoffMobileNumber,
        ?string $earliestPickupTime = null,
        string $instructions = 'Make the delivery possible ASAP',
        string $countryCode = 'GB',
        bool $isConfirmed = true
    ): array {
        $parcelData = [
            'parcel_external_id' => $parcelExternalId,
            'parcel_reference_number' => $parcelReferenceNumber,
            'parcel_description' => $parcelDescription,
            'width' => $width,
            'length' => $length,
            'height' => $height,
            'weight' => $weight,
        ];

        return [
            'is_confirmed' => $isConfirmed ? 1 : 0,
            'external_id' => $externalId,
            'pickups' => [
                [
                    'earliest_pickup_time' => $earliestPickupTime ?? CompanyStandardsServices::getStandardPickUpTime()->toIso8601String(),
                    'pickup_city' => $pickupCity,
                    'pickup_address1' => $pickupAddress,
                    'pickup_postcode' => $pickupPostcode,
                    'pickup_country_code' => $countryCode,
                    'pickup_location_lat' => $pickupLat,
                    'pickup_location_lng' => $pickupLon,
                    'pickup_person_name' => $pickupPersonName,
                    'pickup_mobile_number' => $pickupMobileNumber,
                    'parcels' => [$parcelData],
                ],
            ],
            'dropoffs' => [
                [
                    'dropoff_city' => $dropoffCity,
                    'dropoff_address1' => $dropoffAddress,
                    'dropoff_postcode' => $dropoffPostcode,
                    'dropoff_country_code' => $countryCode,
                    'dropoff_location_lat' => $dropoffLat,
                    'dropoff_location_lng' => $dropoffLon,
                    'dropoff_person_name' => $dropoffPersonName,
                    'dropoff_email' => $dropoffEmail,
                    'dropoff_mobile_number' => $dropoffMobileNumber,
                    'dropoff_instructions' => $instructions,
                    'parcels' => [$parcelData],
                ],
            ],
        ];
    }

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
            CURLOPT_URL => self::getApiUrl().'/jobs?XDEBUG_SESSION=1',
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
                'Api-Key: '.self::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->errors)) {
            report(json_encode($response->errors));
            throw new Exception($response->errors[0]->message);
        }

        return $response;
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public static function getJobPricing(array $job): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::getApiUrl().'/quotes',
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
                'Api-Key:'.self::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->errors)) {
            report(json_encode($response->errors));
            throw new Exception($response->errors[0]->message);
        }

        return $response;
    }

    public static function getJob(string $jobId): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::getApiUrl().'/jobs/'.$jobId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Api-Key:'.self::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->errors)) {
            report(json_encode($response->errors));
            throw new Exception($response->errors[0]->message);
        }

        return $response;
    }

    public static function cancelJob(string $jobId, GophrCancellationReasonEnum $cancellationReason): stdClass
    {
        $formData = [
            'cancelled_reason' => $cancellationReason->value,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::getApiUrl().'/jobs/'.$jobId.'/cancel',
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
                'Api-Key:'.self::getApiKey(),
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->errors)) {
            report(json_encode($response->errors));
            throw new Exception($response->errors[0]->message);
        }

        return $response;
    }
}
