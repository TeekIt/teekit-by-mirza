<?php

namespace App\Services;

use stdClass;

final class StripeServices
{
    public static function getSecretKey(): string
    {
        return config('stripe.STRIPE_SECRET_KEY');
    }

    public static function getPublishKey(): string
    {
        return config('stripe.STRIPE_PUBLISH_KEY');
    }

    public static function createCustomer(string $name, string $email): stdClass
    {
     $curl = curl_init();

        $formData = [
            'name' => $name,
            'email' => $email,
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);   
    }

    public static function createPaymentIntentAndSavePaymentMethod(): stdClass
    {
        $curl = curl_init();

        $customer = static::createCustomer($_REQUEST['name'], $_REQUEST['email']);

        $formData = [
            'customer' => $customer->id,
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'setup_future_usage' => 'off_session',
            'automatic_payment_methods[enabled]' => "true"
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function createPaymentIntent(): stdClass
    {
        $curl = curl_init();

        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
        ];
       
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function requestPaymentAuthorization(): stdClass
    {
        $curl = curl_init();

        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
        ];
        
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function requestIncrementalAuthorizationSupport(): stdClass
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

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function performIncrementalAuthorization(?string $paymentIntentId = null, ?int $amount = null): stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;
        $formData = [
            'amount' => $_REQUEST['amount'] ?? $amount,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/increment_authorization');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function capturePaymentIntent(?string $paymentIntentId = null, ?int $amount = null): string|stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $formData = [
            'amount_to_capture' => $_REQUEST['amount'] ?? $amount,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/capture');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function refundPaymentIntent(?string $paymentIntentId = null): string|stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'. $paymentIntentId .'/cancel');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_USERPWD, static::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl))
            echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }
}
