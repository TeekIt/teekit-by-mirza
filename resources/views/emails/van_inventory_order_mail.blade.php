<x-mail::message>
<b>Dear {{ $sellerName }},</b>

You have received a new van inventory order. Below are the details of the products ordered:

<x-mail::table>
| Product | Image | Price | Qty |
|:--------|:-----:|------:|----:|
@foreach ($orderItems as $singleIndex)
| {{ $singleIndex['title'] }} | <img src="{{ $singleIndex['image'] }}" width="50" height="50" alt="Product Image"> | £{{ number_format($singleIndex['price'], 2) }} | {{ $singleIndex['qty'] }} |
@endforeach
</x-mail::table>

**Van Location:** {{ $vanLocation }}

@include('layouts.email.footer')
</x-mail::message>
