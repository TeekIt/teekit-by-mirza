<x-mail::message>

<b>Dear {{ $order->buyer->name }},</b>

We regret to inform you that your recent <b>order #{{ $order->id }}</b> from <b>{{ $order->seller->name }}</b> has been canceled by the seller.
Unfortunately, the seller is unable to complete your order at this time. But don't worry <b>as you have been refunded</b> for this transaction.

We apologize for any inconvenience this may have caused and appreciate your understanding.

If you require any assistance or have any questions, please do not hesitate to contact us at: <b>{{ config('constants.ADMIN_EMAIL') }}</b>

@include('layouts.email.footer')
</x-mail::message>
