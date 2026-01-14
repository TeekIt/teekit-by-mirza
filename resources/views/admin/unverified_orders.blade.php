@extends('layouts.admin.app')
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0 text-dark text-center">Unverified Orders</h1>
                <div class="float-right">
                    <!-- <button type="button" class="btn btn-success" onclick="selectAll()">
                        <a class="text-white">Select All</a>
                    </button> -->
                    <button type="button" class="btn btn-danger" onclick="delOrders()">
                        <a class="text-white">Delete</a>
                    </button>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <form action="" class="w-100 mb-3">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" class="form-control" name="search" placeholder="Order #">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row">
            @foreach($orders as $order)
            <div class="col-md-12 p-4 pr-4">
                <div class="card">
                    <div class="card-body p-2 pl-5 pr-5 pb-5">
                        <div class="p-2 mb-2">
                            <input type="checkbox" class="select-checkbox" title="Select" id="{{$order->order_id}}">
                            Order #{{$order->order_id}}
                            @if($order->order_status=='pending')
                            <a href="{{route('accept_order',['order_id'=>$order->id])}}"
                                class="d-block btn btn-success float-right">Click Here If Order Is Ready</a>
                            <a href="{{route('cancel_order',['order_id'=>$order->id])}}"
                                class="d-block btn btn-danger float-right" onclick="cancelOrder(event)"
                                style="margin-right: 20px">Cancel Order</a>
                            @elseif ($order->order_status=='ready')
                            <a href="{{route('mark_as_delivered',['order_id'=>$order->id])}}"
                                class="d-block btn btn-success float-right">Mark As Delivered</a>
                            @elseif ($order->order_status=='delivered')
                            <a href="{{route('mark_as_completed',['order_id'=>$order->id])}}"
                                class="d-block btn btn-success float-right">Mark As Completed</a>
                            @endif
                        </div>
                        <div class="card-text">
                            <div class="row">
                                <div class="col-md-4">
                                    <p>
                                        Placed on {{$order->order_details->created_at}}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            Order Status: <span
                                                class="text-warning">{{$order->order_details->order_status}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <i class="far fa-times-circle text-danger"></i>
                                            <span class="text-danger">Unverified</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row">
                                        <a href="{{route('verify_order',['order_id'=>$order->order_id])}}"
                                            class="d-block btn btn-success float-right" onclick="clickToVerify(event)"
                                            style="margin-right: 20px">Click To
                                            Verify</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr>
                            </div>
                            <div class="col-md-12">
                                @foreach($order->items as $item)
                                <div class="row mb-2">
                                    <div class="col-md-2">
                                        <span class="img-container">
                                            @if(str_contains($item->product->feature_img, 'https://'))
                                            <img class="d-block m-auto " src="{{$item->product->feature_img}}">
                                            @else
                                            <img class="d-block m-auto "
                                                src="{{config('constants.BUCKET') . $item->product->feature_img}}">
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                            <a href="#"
                                                class="d-block text-site-primary">{{$item->product->product_name}}</a>
                                            <a href="#"
                                                class="d-block text-site-primary">{{$item->product->category->category_name ?? ''}}</a>
                                            <a href="#" class="d-block text-site-primary">{{$item->product->sku}}</a>
                                        </h3>
                                    </div>
                                    <div class="col-md-6 mt-5">
                                        <strong class="text-site-primary"> Receiver Name: </strong>
                                        {{$order->order_details->receiver_name}} <br>
                                        <strong class="text-site-primary"> Contact: </strong>
                                        {{$order->order_details->phone_number}} <br>
                                        <strong class="text-site-primary"> Address: </strong>
                                        {{$order->order_details->address}} <br>
                                        <strong class="text-site-primary"> House#: </strong>
                                        {{$order->order_details->house_no}} <br>
                                        <strong class="text-site-primary"> Flat: </strong>
                                        {{$order->order_details->flat}} <br>
                                        <strong class="text-site-primary"> QTY: </strong> {{$item->product_qty}}
                                    </div>
                                    <div class="col-md-12"><br></div>
                                </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-md-12">
                {{ $orders_p->links() }}
            </div>
        </div>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->
@endsection

@section('scripts')
<script>
function cancelOrder(ev) {
    ev.preventDefault();
    var urlToRedirect = ev.currentTarget.getAttribute(
        'href'
    ); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
    Swal.fire({
        title: 'Warning!',
        text: 'Are you sure you want to cancel this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed)
            window.location.href = urlToRedirect
    });
}

function clickToVerify(ev) {
    ev.preventDefault();
    var urlToRedirect = ev.currentTarget.getAttribute(
        'href'
    ); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
    Swal.fire({
        title: 'Warning!',
        text: 'Are you sure you want to verify this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed)
            window.location.href = urlToRedirect
    });
}
</script>
@endsection