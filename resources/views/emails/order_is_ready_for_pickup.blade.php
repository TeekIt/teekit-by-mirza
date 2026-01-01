<x-mail::message>

<b>Dear {{ $order->buyer->name }},</b>

We are pleased to inform you that your <b>order #{{ $order->id }}</b> is now ready for pickup.

Please collect your order from the following address:<br>
<b>{{ $seller->business_name }}, {{ $seller->full_address }}</b>

<x-mail::button :url="$pinLocation">
View In Map
</x-mail::button>

For any inquiries, please contact us at<br>
Phone: <b>{{$seller->business_phone}}</b><br>
Email: <b>{{$seller->email}}</b>

We look forward to serving you.

@include('layouts.email.footer')
</x-mail::message>
