<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;
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

    public static function calculateCharge(int $amount, string $currency = 'GBP'): int
    {
        /* Convert to Cents or lowest unit of given Currency according to Stripe standards */
        return bcmul($amount, 100, 0);
    }

    public static function getSingleChargeCheckoutForm(
        int $totalCharge,
        string $productName,
        string $successUrl,
        string $cancelUrl,
        int $qty = 1
    ): Checkout {
        return request()->user()->checkoutCharge(
            self::calculateCharge($totalCharge),
            $productName,
            $qty,
            [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]
        );
    }

    public static function createStandardConnectAccount(User $user): stdClass
    {
        $curl = curl_init();

        $formData = [
            'type' => 'standard',
            'email' => $user->email,
            'business_type' => 'company',
            'company' => [
                'name' => $user->business_name,
                'address' => [
                    'city' => $user->city,
                    'line1' => $user->full_address,
                ],
            ],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/accounts');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey().':');

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->error)) {
            Log::error(json_encode($response->error));
            throw new Exception($response->error->message);
        }

        return $response;
    }

    public static function createConnectAccountLink(string $accountId, string $refreshUrl, string $returnUrl): stdClass
    {
        $curl = curl_init();

        $formData = [
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/account_links');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey().':');

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->error)) {
            Log::error(json_encode($response->error));
            throw new Exception($response->error->message);
        }

        return $response;
    }

    public static function getConnectAccountLink(User $user): stdClass
    {
        $response = StripeServices::createStandardConnectAccount($user);

        return StripeServices::createConnectAccountLink(
            $response->id,
            config('constants.LIVE_DASHBOARD_URL'),
            config('constants.LIVE_DASHBOARD_URL')
        );
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
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

        curl_close($curl);

        return json_decode($data);
    }

    public static function createPaymentIntentAndSavePaymentMethod(): stdClass
    {
        $curl = curl_init();

        $customer = self::createCustomer($_REQUEST['name'], $_REQUEST['email']);

        $formData = [
            'customer' => $customer->id,
            'amount' => $_REQUEST['amount'],
            'currency' => $_REQUEST['currency'],
            'setup_future_usage' => 'off_session',
            'off_session' => 'true',
            'confirm' => 'true',
            'automatic_payment_methods[enabled]' => 'true',
            'payment_method' => $_REQUEST['paymentMethodId'],
        ];

        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

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
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

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
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

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
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

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
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'.$paymentIntentId.'/increment_authorization');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

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
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'.$paymentIntentId.'/capture');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

        curl_close($curl);

        return json_decode($data);
    }

    public static function refundPaymentIntent(?string $paymentIntentId = null): string|stdClass
    {
        $paymentIntentId = $_REQUEST['paymentIntentId'] ?? $paymentIntentId;

        $formData = [
            'payment_intent' => $paymentIntentId,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/refunds/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_USERPWD, self::getSecretKey());

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error:'.curl_error($curl);
        }

        curl_close($curl);

        return json_decode($data);
    }
}
