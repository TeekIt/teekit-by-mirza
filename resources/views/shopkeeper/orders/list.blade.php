@extends('layouts.shopkeeper.app')
@section('content')
<div class="content">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Orders</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <form action="" class="w-100">
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
                            <div class="p-2 mb-2">Order #{{$order->id}} @if($order->order_status=='pending')
                                <a href="{{route('accept_order',['order_id'=>$order->id])}}" class=" d-block btn btn-warning float-right">Click when preparing order</a>
                                <a href="{{route('cancel_order',['order_id'=>$order->id])}}" onclick="cancelOrder(event)" class=" d-block btn btn-danger float-right" style="margin-right: 20px">Cancel Order</a>
                                @else
                                @if(!empty($order->delivery_boy_id))
                                <a href="" data-bs-toggle="modal" data-bs-target="#detailsModal{{$order->id}}" class=" btn btn-primary d-block float-right">View Driver Details</a>
                                <?php
                                $user = \App\User::find($order->delivery_boy_id);
                                ?>
                                @if(!empty($user))
                                <div class="modal fade" id="detailsModal{{$order->id}}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailsModalLabel">{{$user->name}} {{$user->l_name}}</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                $fields = [
                                                    'is_online',
                                                    'is_active',
                                                    'business_name',
                                                    'business_location',
                                                    'business_hours',
                                                    'bank_details',
                                                    'settings',
                                                    'user_img',
                                                    'remember_token',
                                                    'created_at',
                                                    'updated_at',
                                                    'pending_withdraw',
                                                    'total_withdraw',
                                                    'application_fee',
                                                    'temp_code'
                                                ];
                                                ?>
                                                <div class="row">

                                                    @foreach(json_decode($user) as $key=>$u)
                                                    @if(!empty($u) && !in_array($key,$fields))
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="" class="text-capitalize">{{str_replace('_',' ',$key)}}</label>
                                                            <input type="text" disabled class="form-control" value="{{$u}}">
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="" class="mt-5">
                                                                @if($user->is_active==0)
                                                                <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>1])}}"> <span class="text-success">Click here to Enable Account</span></a>
                                                                @else
                                                                <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>0])}}"> <span class="text-danger">Click here to Disable Account </span></a>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="modal-footer hidden d-none">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endif
                                @endif
                            </div>

                            <div class="card-text">
                                <div class="row">

                                    <div class="col-md-6 text-lg">
                                        <p>
                                            <b>Placed on:</b> {{$order->created_at}} <b> Order Total: </b> £{{$order->order_total}}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <b>Order Status:</b> <span class=" badge badge-warning">{{$order->order_status}}</span>
                                                <b>Order Type:</b> <span class=" badge badge-info">{{$order->type}}</span>
                                                <b>Payment Status:</b> <span class=" badge badge-primary">{{$order->payment_status}}</span>
                                            </div>
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
                                                <img class="d-block m-auto" src="{{asset($item->product->feature_img)}}" alt="">
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <h3 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th>Product Name: </th>
                                                        <td>{{$item->product->product_name}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Category: </th>
                                                        <td>{{$item->product->category->category_name}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>SKU: </th>
                                                        <td>{{$item->product->sku}}</td>
                                                    </tr>
                                                </table>
                                            </h3>
                                        </div>
                                        <div class="col-md-2 mt-5 text-lg">
                                            <b class="text-site-primary text-lg">QTY:</b> {{$item->product_qty}}
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
</div>
@endsection

@section('scripts')
<script>
    function cancelOrder(ev) {
        ev.preventDefault();
        var urlToRedirect = ev.currentTarget.getAttribute('href'); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
        Swal.fire({
            title: 'Warning!',
            text: 'Are you sure that you want to cancel this order?',
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