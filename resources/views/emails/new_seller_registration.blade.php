<x-mail::message>

We are pleased to inform you that a new <b>{{ $sellerRoleName }} Seller</b> has just signed up on our platform. <br>
Please review their details provided below & proceed with the verification process to determine whether to approve or disapprove this seller on our platform.

<x-mail::table>
|               |               |          |
| ------------- |:-------------:| --------:|
@if ($parentSeller)
| <b>Parent</b>      | {{ $parentSeller }}|
@endif
| <b>Store</b>      | {{ $seller->business_name }}|
| <b>Owner</b>      | {{ $seller->name }} |
| <b>Email</b>      | {{ $seller->email }} |
| <b>Contact</b>    | {{ $seller->country_code }} {{ $seller->business_phone }}|
| <b>Address</b>    | {{ $seller->full_address }}|
</x-mail::table>

<x-mail::button :url="$accountVerificationLink">
Verify Now
</x-mail::button>

If you cannot verify the above given details & you don't want to allow this seller to be on our platform then just leave this mail without performing any action.

@include('layouts.email.footer')
</x-mail::message>
