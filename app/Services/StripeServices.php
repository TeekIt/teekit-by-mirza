<?php

namespace App\Services;

use App\Orders;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;

final class StripeServices
{
    public static function getLiveApiKey()
    {
        return config('constants.STRIPE_LIVE_API_KEY');
    }

    public static function getTestApiKey()
    {
        return config('constants.STRIPE_TEST_API_KEY');
    }

    public static function createPaymentIntent()
    {
        $ch = curl_init();
        $query_params = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency']
        ];
        // $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseServices::getApiResponse(
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
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/request_incremental_authorization_support') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseServices::getApiResponse(
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
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/perform_incremental_authorization') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/increment_authorization');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseServices::getApiResponse(
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
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/capture') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_params));
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($ch);
        if (curl_errno($ch)) echo 'Error:' . curl_error($ch);

        curl_close($ch);
        return JsonResponseServices::getApiResponse(
            json_decode($data),
            true,
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function refundCustomer(Orders $order)
    {
        $api_key = (url('/') === config('constants.LIVE_DASHBOARD_URL')) ? static::getLiveApiKey() : static::getTestApiKey();
        // Stripe::setApiKey($api_key);
        // Refund::create([
        //     // 'charge' => $order->transaction_id,
        //     'payment_intent' => $order->payment_intent,
        //     'reason' => 'requested_by_customer'
        // ]);

        $stripe = new StripeClient($api_key); //new \Stripe\StripeClient($api_key);
        // $stripe->refunds->create([
        //     'payment_intent' => $order->payment_intent,
        //     'reason' => 'requested_by_customer'
        // ]);

        $stripe->refunds->create([
           'payment_intent' => 'pi_1GszsK2eZvKYlo2CfhZyoZLp',
           'reason' => 'requested_by_customer' 
        ]);
        // $stripe->refunds->create(['charge' => 'ch_1NirD82eZvKYlo2CIvbtLWuY']);
    }
}
