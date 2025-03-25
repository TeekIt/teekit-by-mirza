<x-mail::message>

A new custom product order has been created by a buyer.<br>
Following are the details of the custom product:
<x-mail::table>
|               |               |          |
| ------------- |:-------------:| --------:|
| <b>Image</b>      | <img width="200px" src="{{ asset(config('constants.BUCKET') . $order->order_items[0]->product->feature_img) }}"> |
| <b>Name</b>      | {{ $order->order_items[0]->product->product_name }} |
| <b>Max Price</b>      | {{ $order->order_items[0]->product->max_price }} |
| <b>Qty</b>      | {{ $order->order_items[0]->product->qty }} |
</x-mail::table>

@include('layouts.email.footer')
</x-mail::message>
