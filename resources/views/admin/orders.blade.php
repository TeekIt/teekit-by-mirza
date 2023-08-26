@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Orders</h1>
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
                @foreach ($orders as $order)
                    <div class="col-md-12 p-4 pr-4">
                        <div class="card">
                            <div class="card-body p-2 pl-5 pr-5 pb-5">
                                <div class="p-2 mb-2 d-flex justify-content-between">
                                    <div>
                                        <input type="checkbox" class="select-checkbox" title="Select"
                                            id="{{ $order->id }}">
                                        Order #{{ $order->id }}
                                    </div>
                                    @if ($order->order_status == 'pending')
                                        <div>
                                            <a href="{{ route('accept_order', ['order_id' => $order->id]) }}"
                                                class="d-block btn btn-success float-right">Click Here If Order Is Ready</a>
                                            <a href="{{ route('cancel_order', ['order_id' => $order->id]) }}"
                                                class="d-block btn btn-danger float-right" onclick="cancelOrder(event)"
                                                style="margin-right: 20px">Cancel Order</a>
                                        </div>
                                    @elseif ($order->order_status == 'ready')
                                        <div>
                                            <a data-bs-toggle="modal" data-bs-target="#stuartModel{{ $order->id }}"
                                                class="d-block btn btn-warning float-left mx-1">Assign To Stuart</a>
                                            <a href="{{ route('mark_as_delivered', ['order_id' => $order->id]) }}"
                                                class="d-block btn btn-success float-right">Mark As Delivered</a>
                                        </div>
                                    @elseif ($order->order_status == 'stuartDelivery')
                                        <div>
                                            <button id="loader_btn_{{ $order->id }}" class="btn btn-success d-none">
                                                <div class="spinner-border text-white" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </button>
                                            <button id="status_btn_{{ $order->id }}"
                                                onclick="getStuartStatus({{ $order->id }})"
                                                class="btn btn-success float-right">Check Status</button>
                                        </div>
                                    @elseif ($order->order_status == 'delivered')
                                        <div>
                                            <a href="{{ route('mark_as_completed', ['order_id' => $order->id]) }}"
                                                class="d-block btn btn-success float-right">Mark As Completed</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>
                                                Placed on {{ $order->created_at }}
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    Order Status: <span
                                                        class="text-warning">{{ $order->order_status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <hr>
                                    </div>
                                    <div class="col-md-12">
                                        @foreach ($order->items as $item)
                                            <div class="row mb-2">
                                                <div class="col-md-2">
                                                    <span class="img-container">
                                                        @if (str_contains($item->product->feature_img, 'https://'))
                                                            <img class="d-block m-auto "
                                                                src="{{ $item->product->feature_img }}" alt="">
                                                        @else
                                                            <img class="d-block m-auto "
                                                                src="{{ config('constants.BUCKET') . $item->product->feature_img }}">
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="col-md-4">
                                                    <h3 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                        <a href="#"
                                                            class="d-block text-site-primary">{{ $item->product->product_name }}</a>
                                                        <a href="#"
                                                            class="d-block text-site-primary">{{ $item->product->category->category_name ?? '' }}</a>
                                                        <a href="#"
                                                            class="d-block text-site-primary">{{ $item->product->sku }}</a>
                                                    </h3>
                                                </div>
                                                <div class="col-md-6 mt-5">
                                                    <strong class="text-site-primary"> Receiver Name: </strong>
                                                    {{ $order->receiver_name }} <br>
                                                    <strong class="text-site-primary"> Contact: </strong>
                                                    {{ $order->phone_number }}
                                                    <br>
                                                    <strong class="text-site-primary"> Address: </strong>
                                                    {{ $order->address }} <br>
                                                    <strong class="text-site-primary"> House#: </strong>
                                                    {{ $order->house_no }} <br>
                                                    <strong class="text-site-primary"> Flat: </strong> {{ $order->flat }}
                                                    <br>
                                                    <strong class="text-site-primary"> QTY: </strong>
                                                    {{ $item->product_qty }}
                                                    <br>
                                                    <strong class="text-site-primary"> User Choice: </strong>
                                                    @if ($item->user_choice == 1)
                                                        <b>Alternative product that does the job</b>
                                                        <a href="#" class="d-block btn btn-warning">Select Alternative
                                                            Product</a>
                                                    @elseif ($item->user_choice == 2)
                                                        <b>Remove only this product from order</b>
                                                        <a href="{{ route('remove_order_product', [
                                                            'order_id' => $order->id,
                                                            'item_id' => $item->id,
                                                            'product_price' => $item->product_price,
                                                            'product_qty' => $item->product_qty,
                                                        ]) }}"
                                                            class="d-block btn btn-dark">Remove</a>
                                                    @elseif ($item->user_choice == 3)
                                                        {{-- 3 == Search for product in other stores --}}
                                                        <b>Don't worry! if you don't have this product the user will search
                                                            for an alternative product by himself if you remove this</b>
                                                    @elseif ($item->user_choice == 4)
                                                        {{-- 4 == Request a call from the store --}}
                                                        <b>Call the user if this product is out of stock</b>
                                                    @elseif ($item->user_choice == 5)
                                                        <b>Cancel the order if this product is out of stock</b>
                                                        <a href="{{ route('cancel_order', ['order_id' => $order->id]) }}"
                                                            class="d-block btn btn-danger" onclick="cancelOrder(event)">
                                                            Cancel Order
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="col-md-12"><br></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="stuartModel{{ $order->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="post" action="{{ route('stuart.job.creation') }}"
                                    enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="modal-header">
                                        <h5 class="modal-title display-center" id="exampleModalLabel">Add Custom Order Id</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="">Order #</label>
                                                    <input type="text" name="custom_order_id" placeholder="Enter custom order id or leave blank..." class="form-control"
                                                        autofocus>
                                                    <input type="hidden" name="order_id" class="form-control"
                                                        value="{{ $order->id }}" required autofocus>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer hidden">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            Assign
                                        </button>
                                    </div>
                                </form>
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
    <div class="modal fade" id="orderStatusModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between">
                        <p class="mb-0">Stuard Delivery Status</p>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <hr class="mt-2 mb-4"
                        style="height: 0; background-color: transparent; opacity: .75; border-top: 2px dashed #9e9e9e;">
                    <div id="driver_div">
                        <div class="d-flex justify-content-between">
                            <p class="fw-bold mb-0">Driver Name</p>
                            <p class="mb-0" id="driver_name"></p>
                        </div>

                        <div class="d-flex justify-content-between">
                            <p class="fw-bold mb-0">Phone No</p>
                            <p class="mb-0" id="driver_phone"></p>
                        </div>

                        <div class="d-flex justify-content-between pb-1">
                            <p class="fw-bold">Transport Type</p>
                            <p id="driver_transport_type"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p class="fw-bold">Status</p>
                        <p class="fw-bold text-success" id="status"></p>
                    </div>

                </div>
                <div class="modal-footer d-flex justify-content-center border-top-0 py-4">
                    <a id="track_btn" target="_blank" class="btn btn-success btn-lg mb-1 text-white">
                        Track your order
                    </a>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
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
                text: 'Are you sure want to cancel this order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed)
                    window.location.href = urlToRedirect
            });
        }

        function getStuartStatus(order_id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('stuart.job.status') }}",
                data: {
                    'order_id': order_id,
                    '_token': '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    $('#loader_btn_' + order_id).removeClass('d-none');
                    $('#status_btn_' + order_id).addClass('d-none');
                },
                success: function(res) {
                    if (res.message == 'completed') {
                        location.reload();
                    } else {
                        var data = res.data;
                        $('#status_btn_' + order_id).removeClass('d-none');
                        $('#loader_btn_' + order_id).addClass('d-none');
                        if (data.driver === null) {
                            $('#driver_div').addClass('d-none');
                        } else {
                            $('#driver_name').text(data.driver.display_name);
                            $('#driver_phone').text(data.driver.phone);
                            $('#driver_transport_type').text(data.driver.transport_type);
                        }
                        if (data.status == 'finished') {
                            $('#status').removeClass('text-success');
                            $('#status').addClass('text-danger');
                        } else {
                            $('#status').addClass('text-success');
                            $('#status').removeClass('text-danger');
                        }
                        $('#status').text(data.status);
                        $('#track_btn').attr('href', data.deliveries[0].tracking_url);
                        $('#orderStatusModal').modal('show');
                    }
                }
            });
        }

        function closeModal(id) {
            $('#' + id).modal().hide();
        }
    </script>
@endsection
