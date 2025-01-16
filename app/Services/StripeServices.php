<?php

namespace App\Services;

use App\Orders;
use Illuminate\Http\JsonResponse;
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

        $formData = [
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
        ];

        $apiKey = (app()->environment('production')) ? static::getLiveApiKey() : static::getTestApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

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

        $apiKey = (app()->environment('production')) ? static::getLiveApiKey() : static::getTestApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);

        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

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

        $apiKey = (app()->environment('production')) ? static::getLiveApiKey() : static::getTestApiKey();

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function performIncrementalAuthorization(string $paymentIntentId = null, int $amount = null)
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;
        $formData = [
            'amount' => $_REQUEST['amount'] ?? $amount,
        ];

        $apiKey = (app()->environment('production')) ? static::getLiveApiKey() : static::getTestApiKey();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/increment_authorization');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);

        // if (!isset(json_decode($data)->error)) {
        //     return JsonResponseServices::getApiResponse(
        //         json_decode($data),
        //         config('constants.TRUE_STATUS'),
        //         '',
        //         config('constants.HTTP_OK')
        //     );
        // }

        // return JsonResponseServices::getApiResponse(
        //     json_decode($data),
        //     config('constants.FALSE_STATUS'),
        //     '',
        //     config('constants.HTTP_UNPROCESSABLE_REQUEST')
        // );
    }

    public static function capturePaymentIntent(string $paymentIntentId = null, int $amount = null)
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $formData = [
            'amount_to_capture' => $_REQUEST['amount'] ?? $amount,
        ];

        $apiKey = (app()->environment('production')) ? static::getLiveApiKey() : static::getTestApiKey();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/capture');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);

        $data = curl_exec($curl);
        if (curl_errno($curl)) echo 'Error:' . curl_error($curl);

        curl_close($curl);

        return json_decode($data);
    }

    public static function refundCustomer(Orders $order)
    {
        $apiKey = (url('/') === config('constants.LIVE_DASHBOARD_URL')) ? static::getLiveApiKey() : static::getTestApiKey();
        // Stripe::setApiKey($apiKey);
        // Refund::create([
        //     // 'charge' => $order->transaction_id,
        //     'payment_intent' => $order->payment_intent,
        //     'reason' => 'requested_by_customer'
        // ]);

        $stripe = new StripeClient($apiKey); //new \Stripe\StripeClient($apiKey);
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
