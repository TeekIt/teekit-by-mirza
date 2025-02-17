<x-mail::message>
<b>Dear {{ $seller->name }},</b>

<b>Welcome to {{ env('APP_NAME') }}!</b><br>
We are pleased to inform you that your store has been approved. You can now login to your dashboard & start selling.

<x-mail::button :url="config('constants.LIVE_DASHBOARD_URL')">
Login To Your Dashboard
</x-mail::button>

Thank you for choosing <b>{{ env('APP_NAME') }}</b>. If you have any questions or need assistance, please don't hesitate to reach out to our support team.

Best regards,<br>
<b>Team {{ env('APP_NAME') }}</b>

@include('layouts.email.footer')
</x-mail::message>
