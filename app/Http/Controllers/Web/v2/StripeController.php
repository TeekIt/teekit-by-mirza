<?php

namespace App\Http\Controllers\Web\v2;

use App\Jobs\SendRegeneratedStripeConnectAccMailJob;
use App\Services\StripeServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\RegenerateStripeConnectAccLinkRequest;
use App\User;

class StripeController extends Controller
{
    public function regenerateConnectAccountLink(RegenerateStripeConnectAccLinkRequest $request)
    {
        $validatedData = (object) $request->validated();

        $user = User::getUserByID($validatedData->id);

        SendRegeneratedStripeConnectAccMailJob::dispatch($user)->onQueue('high');

        return view('shopkeeper.stripe.connect_acc_regenerated_link_sent');
    }

    public function getCheckoutFormForRequestedDelivery(Request $request)
    {
        return StripeServices::getSingleChargeCheckoutForm(
            totalCharge: $request->route('totalCharge'),
            productName: $request->route('productName'),
            successUrl: route('seller.requested.deliveries'),
            cancelUrl: route('seller.request.delivery.form')
        );
    }
}
