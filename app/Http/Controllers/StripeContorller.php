<?php

namespace App\Http\Controllers;

use App\Services\JsonResponseServices;
use App\Services\StripeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StripeContorller extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        return JsonResponseServices::getApiResponse(
            StripeServices::createPaymentIntent(),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function requestPaymentAuthorization(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        return JsonResponseServices::getApiResponse(
            StripeServices::requestPaymentAuthorization(),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function capturePaymentIntent(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'paymentIntentId' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $response = StripeServices::capturePaymentIntent();
        $error = isset($response->error);
        
        return JsonResponseServices::getApiResponse(
            $response,
            ($error) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            '',
            ($error) ? config('constants.HTTP_UNPROCESSABLE_REQUEST') : config('constants.HTTP_OK')
        );
    }

    public function refundPaymentIntent(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'paymentIntentId' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $response = StripeServices::refundPaymentIntent();
        $error = isset($response->error);
        
        return JsonResponseServices::getApiResponse(
            $response,
            ($error) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            '',
            ($error) ? config('constants.HTTP_UNPROCESSABLE_REQUEST') : config('constants.HTTP_OK')
        );
    }

    public function requestIncrementalAuthorizationSupport(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'stripeAccountId' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        return JsonResponseServices::getApiResponse(
            StripeServices::requestIncrementalAuthorizationSupport(),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    public function performIncrementalAuthorization(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'paymentIntentId' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        return JsonResponseServices::getApiResponse(
            StripeServices::performIncrementalAuthorization(),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
