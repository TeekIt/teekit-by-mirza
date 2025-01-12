<x-mail::message>
<b>Dear {{ $buyer->name }},</b>

Congratulations! 🥳<br>
You have successfully registered on <b>{{ env('APP_NAME') }}</b>. To complete your registration, please verify your account by clicking the button below:

<x-mail::button :url="$accountVerificationLink">
Verify Now
</x-mail::button>

If you require any assistance or have any questions, please do not hesitate to contact us at: <b>{{ config('constants.ADMIN_EMAIL') }}</b>

For more information, please visit: <a href="{{ config('constants.LIVE_WEBSITE_URL') }}">{{ config('constants.LIVE_WEBSITE_URL') }}</a>

@include('layouts.email.footer')
</x-mail::message>
