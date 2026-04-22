<x-mail::message>

Hi Dear Seller,

Can you help with a quick stock check please?

If you can reply in the next 10–15 minutes, I can confirm and arrange a collection/delivery.

Please reply to this email with:<br>
<b>In stock? (Yes/No)</b><br>
<b>Price ex VAT (and inc VAT if easier)?</b><br>
<b>Earliest collection time today?</b><br><br>

<b>Item needed:</b>
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

Thanks,<br>
Azim<br>
Teek It<br>
{{ config('constants.HEAD_OFFICE_CONTACT') }}

@include('layouts.email.footer')
</x-mail::message>
