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
        return env('STRIPE_LIVE_API_KEY');
    }

    public static function getTestApiKey()
    {
        return env('STRIPE_TEST_API_KEY');
    }

    public static function createPaymentIntent()
    {
        $curl = curl_init();
        $form_data = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency']
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($curl, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);
        return JsonResponseServices::getApiResponse(
            json_decode($data),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function requestIncrementalAuthorizationSupport()
    {
        $curl = curl_init();
        $form_data = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'payment_method_types[]' => 'card',
            'capture_method' => 'manual',
            'payment_method_options[card][request_incremental_authorization_support]' => 'true',
	    'transfer_data' => ['destination' => 'acct_1N1rrrIjHZHlX00M'],
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/request_incremental_authorization_support') ? static::getTestApiKey() : static::getLiveApiKey();
        
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($curl, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);
        return JsonResponseServices::getApiResponse(
            json_decode($data),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function performIncrementalAuthorization()
    {
        $curl = curl_init();
        $payment_intent_id = $_REQUEST['payment_intent_id'];
        $form_data = [
            'amount' => $_REQUEST['amount']
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/perform_incremental_authorization') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/increment_authorization');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($curl, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);
        return JsonResponseServices::getApiResponse(
            json_decode($data),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public static function capturePaymentIntent()
    {
        $curl = curl_init();
        $payment_intent_id = $_REQUEST['payment_intent_id'];
        $form_data = [
            'amount_to_capture' => $_REQUEST['amount']
        ];
        $api_key = (request()->getPathInfo() === '/api/payment_intent/test/capture') ? static::getTestApiKey() : static::getLiveApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/capture');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($curl, CURLOPT_USERPWD, $api_key);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);
        return JsonResponseServices::getApiResponse(
            json_decode($data),
            config('constants.TRUE_STATUS'),
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

        return $stripe->refunds->create([
           'payment_intent' => 'pi_3OmYstIiDDGv1gaV2F5Xeu5t',
           'reason' => 'requested_by_customer' 
        ]);
        // $stripe->refunds->create(['charge' => 'ch_1NirD82eZvKYlo2CIvbtLWuY']);
    }
}
