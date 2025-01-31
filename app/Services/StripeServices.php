<?php

namespace App\Services;

use App\Orders;
use Illuminate\Http\JsonResponse;
use stdClass;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;

final class StripeServices
{
    public static function getLiveSecretKey(): string
    {
        return env('STRIPE_LIVE_SECRET_KEY');
    }

    public static function getLivePublishKey(): string
    {
        return env('STRIPE_LIVE_PUBLISH_KEY');
    }

    public static function getTestSecretKey(): string
    {
        return env('STRIPE_TEST_SECRET_KEY');
    }

    public static function getTestPublishKey(): string
    {
        return env('STRIPE_TEST_PUBLISH_KEY');
    }

    public static function createPaymentIntent()
    {
        $curl = curl_init();

        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
        ];

        $apiKey = (app()->environment('production')) ? static::getLivePublishKey() : static::getTestPublishKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function requestPaymentAuthorization()
    {
        $curl = curl_init();

        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
        ];

        $apiKey = (app()->environment('production')) ? static::getLiveSecretKey() : static::getTestSecretKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function requestIncrementalAuthorizationSupport()
    {
        $curl = curl_init();
        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            // 'payment_method' => 'pm_card_amex',
            'payment_method_types' => ['card_present', 'card'],
            'capture_method' => 'manual',
            // 'payment_method_options[card][request_three_d_secure]' => 'any',
            'payment_method_options[card_present][request_incremental_authorization_support]' => 'true',
            'transfer_data' => ['destination' => $_REQUEST['stripeAccountId']],
        ];

        $apiKey = (app()->environment('production')) ? static::getLivePublishKey() : static::getTestPublishKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function performIncrementalAuthorization(string $paymentIntentId = null, int $amount = null)
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;
        $formData = [
            'amount' => $_REQUEST['amount'] ?? $amount,
        ];

        $apiKey = (app()->environment('production')) ? static::getLivePublishKey() : static::getTestPublishKey();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/increment_authorization');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function capturePaymentIntent(string $paymentIntentId = null, int $amount = null): string|stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $formData = [
            'amount_to_capture' => $_REQUEST['amount'] ?? $amount,
        ];

        $apiKey = (app()->environment('production')) ? static::getLiveSecretKey() : static::getTestSecretKey();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/capture');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function refundPaymentIntent(string $paymentIntentId = null): string|stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $apiKey = (app()->environment('production')) ? static::getLiveSecretKey() : static::getTestSecretKey();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'. $paymentIntentId .'/cancel');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }
}
