<x-mail::message>
<b>Hello! From {{ config('app.name') }}</b>

You are receiving this email because we heard that you want to reset your password.

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.

@include('layouts.email.footer')
</x-mail::message>