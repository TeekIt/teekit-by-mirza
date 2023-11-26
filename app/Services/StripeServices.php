<?php

namespace App\Services;

final class StripeServices
{
    public static function createPaymentIntent()
    {
        $ch = curl_init();
        $query_params = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency']
        ];
        // $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test') ? config('constants.STRIPE_TEST_API_KEY') : config('constants.STRIPE_LIVE_API_KEY');

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseCustom::getApiResponse(
            json_decode($data),
            true,
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function requestIncrementalAuthorizationSupport()
    {
        $ch = curl_init();
        $query_params = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'payment_method_types[]' => 'card',
            'capture_method' => 'manual',
            'payment_method_options[card][request_incremental_authorization_support]' => 'true',
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/request_incremental_authorization_support') ? config('constants.STRIPE_TEST_API_KEY') : config('constants.STRIPE_LIVE_API_KEY');

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseCustom::getApiResponse(
            json_decode($data),
            true,
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function performIncrementalAuthorization()
    {
        $ch = curl_init();
        $payment_intent_id = $_REQUEST['payment_intent_id'];
        $query_params = [
            'amount' => $_REQUEST['amount']
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/perform_incremental_authorization') ? config('constants.STRIPE_TEST_API_KEY') : config('constants.STRIPE_LIVE_API_KEY');

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/increment_authorization');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseCustom::getApiResponse(
            json_decode($data),
            true,
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function capturePaymentIntent()
    {
        $ch = curl_init();
        $payment_intent_id = $_REQUEST['payment_intent_id'];
        $query_params = [
            'amount_to_capture' => $_REQUEST['amount']
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/capture') ? config('constants.STRIPE_TEST_API_KEY') : config('constants.STRIPE_LIVE_API_KEY');

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseCustom::getApiResponse(
            json_decode($data),
            true,
            '',
            config('constants.HTTP_OK')
        );
    }
}
