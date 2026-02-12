{{-- This template is deprecated - Remove it if its not required for a long time --}}
{{-- 
<x-mail::message>

A new custom product order has been created by a buyer.<br>
Following are the details of the custom product:
<x-mail::table>
|               |              |
| ------------- |:-------------|
| <b>Image</b>      | <img width="200px" src="{{ asset(config('constants.BUCKET') . $order->order_items[0]->product->feature_img) }}"> |
| <b>Product Name</b>      | {{ $order->order_items[0]->product->product_name }} |
| <b>Qty</b>      | {{ $order->order_items[0]->product->qty }} |
| <b>Category</b>      | {{ $order->order_items[0]->product->category?->category_name }} |
| <b>Budget</b>      | £{{ $order->order_items[0]->product->max_price }} |
| <b>Weight</b>      | {{ $order->order_items[0]->product->weight }}kg |
| <b>Brand</b>      | {{ $order->order_items[0]->product->brand }} |
| <b>Part Number</b>      | {{ $order->order_items[0]->product->part_number }} |
| <b>Colors</b>      | {{ app('App\Services\ProductServices')->jsonDecodeColors($order->order_items[0]->product->colors) }} |
| <b>Transport Vehicle</b>      | {{ $order->order_items[0]->product->transport_vehicle }} |
| <b>Height</b>      | {{ $order->order_items[0]->product->height }} |
| <b>Width</b>      | {{ $order->order_items[0]->product->width }} |
| <b>Length</b>      | {{ $order->order_items[0]->product->length }} |
</x-mail::table>

@include('layouts.email.footer')
</x-mail::message> --}}
