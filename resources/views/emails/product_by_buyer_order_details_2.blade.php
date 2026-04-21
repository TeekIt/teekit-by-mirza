{{-- This template is deprecated - Remove it if its not required for a long time --}}
{{-- 
<x-mail::message>

A new custom product order has been created by a buyer.<br>
Following are the details of the custom product:
<x-mail::table>
|               |              |
| ------------- |:-------------|
| <b>Image</b>      | <img width="200px" src="{{ asset(config('constants.BUCKET') . $order->orderItems[0]->product->feature_img) }}"> |
| <b>Product Name</b>      | {{ $order->orderItems[0]->product->product_name }} |
| <b>Qty</b>      | {{ $order->orderItems[0]->product->qty }} |
| <b>Category</b>      | {{ $order->orderItems[0]->product->category?->category_name }} |
| <b>Budget</b>      | £{{ $order->orderItems[0]->product->max_price }} |
| <b>Weight</b>      | {{ $order->orderItems[0]->product->weight }}kg |
| <b>Brand</b>      | {{ $order->orderItems[0]->product->brand }} |
| <b>Part Number</b>      | {{ $order->orderItems[0]->product->part_number }} |
| <b>Colors</b>      | {{ app('App\Services\ProductServices')->jsonDecodeColors($order->orderItems[0]->product->colors) }} |
| <b>Transport Vehicle</b>      | {{ $order->orderItems[0]->product->transport_vehicle }} |
| <b>Height</b>      | {{ $order->orderItems[0]->product->height }} |
| <b>Width</b>      | {{ $order->orderItems[0]->product->width }} |
| <b>Length</b>      | {{ $order->orderItems[0]->product->length }} |
</x-mail::table>

@include('layouts.email.footer')
</x-mail::message> --}}
