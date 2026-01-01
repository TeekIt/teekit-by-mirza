<x-mail::message>
Dear Seller,

Following is the regenerated Stripe connect account link against your provided information.

To begin processing transactions, please complete your Stripe connect account setup using the link below & do not refresh or close the page until you have filled the whole form:

<x-mail::button :url="$url">
Complete Stripe Account Setup
</x-mail::button>

This secure link will expire in 24 hours. If you need assistance or encounter any issues, please don't hesitate to contact our support team.

@include('layouts.email.footer')
</x-mail::message>