<x-mail::message>
Dear Seller,

Congratulations! 🎉🎊 Your email has been verified

We're pleased to inform you that your email address has been successfully verified. You're now one step away from starting to accept payments on our platform & build your e-commerce store.

To begin processing transactions, please complete your Stripe connect account setup using the link below & do not refresh or close the page until you have filled the whole form:

<x-mail::button :url="$url">
Complete Stripe Account Setup
</x-mail::button>

This secure link will expire in 24 hours. If you need assistance or encounter any issues, please don't hesitate to contact our support team.

We're excited to have you on board!

<small style="font-size: 12px; color: #666;">
If you have closed an unfilled form or have refreshed the page accidentally then you can use the following link to regenerate your Stripe Connect Account Link:
</small>
<br>
<a href="{{ route('stripe.regenerate.connect.account.link', ['id' => $user->id]) }}" style="color: #3490dc; text-decoration: underline;">
Regenerate Link
</a>

@include('layouts.email.footer')
</x-mail::message>