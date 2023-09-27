<div class="content">
    @if (session()->has('error'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Error</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('error') }}
            </div>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Success</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
    {{-- ************************************ Delivery Boy Details Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="deliveryBoyDetailsModal" tabindex="-1" aria-labelledby="deliveryBoyDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModelLabel">Delivery Boy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <tbody class="table-border-bottom-0">
                                    <tr>
                                        <td><b>Name</b></td>
                                        {{-- <td>{{ $f_name }} {{ $l_name }}</td> --}}
                                        <td>James Cameron</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        {{-- <td>{{ $email }}</td> --}}
                                        <td>james@gmail.com</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        {{-- <td>{{ $phone }}</td> --}}
                                        <td>03170155652</td>
                                    </tr>
                                    <tr>
                                        <th>DP</th>
                                        {{-- <td>
                                            <img src=@if ($profile_img) "{{ config('constants.BUCKET') . $profile_img }}"
                                                @else
                                            "{{ asset('/icons/driver.png') }}" @endif width="150px">
                                        </td> --}}
                                        <td>
                                            <img src="{{ asset('/icons/driver.png') }}" width="150px">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Type</th>
                                        {{-- <td>{{ $vehicle_type }}</td> --}}
                                        <td>Bike</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Number</th>
                                        {{-- <td>{{ $vehicle_number }}</td> --}}
                                        <td>99829846</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Search Alternative Product Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="searchAlternativeProductModal" tabindex="-1" aria-labelledby="searchAlternativeProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModelLabel">Search Alternative Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <h5>Please Read Carefully!</h5>
                        <p>
                            <b>Must call the customer before searching an alternative</b> to know customer choice. If you don't call & the customer complains about the alternative product you have selected by yourself then Teekit may cancel your whole order with full money refund to the customer.
                        </p>
                        <p>
                        <h4>Cutomer Contact: +44 3170188986</h4>
                        </p>
                    </div>
                    <form>
                        <div class="row">
                            <div class="col-10 form-floating border border-danger">
                                <input type="text" class="form-control" placeholder="Search alternative product">
                                <label>Search alternative product</label>
                            </div>
                            <div class="col-2 border border-danger">
                                <button class="btn btn-primary py-3 w-100 mx-1 px-0 " title="Add this product to the order">
                                    <span class="fas fa-plus"></span>
                                </button>
                            </div>
                        </div>
                        {{-- Product Container --}}
                        <div class="row mb-2 border border-success">
                            <div class="col-md-2">
                                <span class="img-container">
                                    <img class="d-block m-auto" src="{{ asset('icons/customer.png') }}" alt="">
                                </span>
                            </div>
                            <div class="col-10">
                                <table class="table">
                                    <tr>
                                        <td class="text-site-primary"><b>Product Name:</b></td>
                                        <td>Soudal Multi Purpose Silicone 270ml Clear</td>
                                    </tr>
                                    <tr>
                                        <td class="text-site-primary"><b>Category:</b></td>
                                        <td>Adhesives & Sealants</td>
                                    </tr>
                                    <tr>
                                        <td class="text-site-primary"><b>SKU:</b></td>
                                        <td>LS121644</td>
                                    </tr>
                                    <tr>
                                        <td class="text-site-primary"><b>Available QTY:</b></td>
                                        <td>3</td>
                                    </tr>
                                    <tr>
                                        <td class="text-site-primary"><b>QTY you want to add:</b></td>
                                        <td>
                                            <input type="number" class="form-control col-3">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Confirm
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Header -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-5 col-xl-6">
            <h4 class="py-4 my-1">Orders</h4>
        </div>
        <div class="col-12 col-sm-6 col-md-5 col-xl-5">
            <div class="input-group py-4 my-2">
                <input type="text" wire:model.debounce.500ms="search" class="form-control py-3"
                    placeholder="Search here...">
                <button class="btn btn-site-primary px-4" type="button"><i class='fas fa-search'></i></button>
            </div>
        </div>
        <div class="col-12 col-md-2 col-xl-1">
            {{-- <button type="button" class="btn btn-danger my-3 py-3 w-100" title="Delete selected data" onclick="delUsers()">
                <i class="fas fa-trash-alt"></i>
            </button> --}}
        </div>
    </div>
    <!-- /Content Header -->

    <!-- Main Content -->
    <div class="container border border-danger">
        @for ($x = 0; $x < 3; $x++)
            <!-- Single Order Content -->
            <div class="col-md-12 p-4 pr-4">
                <div class="card">
                    <div class="card-body p-2 pl-5 pr-5 pb-5 border border-dark">
                        <!-- Order Header -->
                        <div class="p-2 mb-2">
                            <table class="table table-striped table-responsive-sm">
                                <thead>
                                    <tr>
                                        <td colspan="4">
                                            <button class="btn btn-warning" title="Click here when preparing order">
                                                Preparing Order
                                            </button>
                                            <button class="btn btn-danger" title="Cancel the whole order">
                                                Cancel Order
                                            </button>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Order#</b></td>
                                        <td>1</td>
                                        <td><b>Order Status</b></td>
                                        <td><span class="badge badge-warning">pending</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Placed At</b></td>
                                        <td>2023-03-15 21:48:35</td>
                                        <td><b>Order Type</b></td>
                                        <td><span class="badge badge-info">self-pickup</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Order Total</b></td>
                                        <td>£10.00</td>
                                        <td><b>Payment Status</b></td>
                                        <td><span class="badge badge-primary">paid</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /Order Header -->

                        <div>
                            @if ('$order->order_status' == 'pending')
                                <a href="{{ route('accept_order', ['order_id' => 1]) }}" class="d-block btn btn-warning float-right">Click when preparing order</a>
                                <a href="{{ route('cancel_order', ['order_id' => 1]) }}" onclick="cancelOrder(event)" class="d-block btn btn-danger float-right" style="margin-right: 20px">
                                    Cancel Order
                                </a>
                            @else
                                @if (!empty($order->delivery_boy_id))
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#detailsModal{{ 1 }}" class="btn btn-primary d-block float-right">
                                        View Driver Details
                                    </a>
                                @endif
                            @endif
                        </div>

                        <div class="card-text border border-info">
                            @for ($i = 0; $i < 3; $i++)
                                <!-- Order Items -->
                                <div class="row mb-2 border border-success">
                                    <div class="col-md-2">
                                        <span class="img-container">
                                            <img class="d-block m-auto" src="{{ asset('icons/customer.png') }}" alt="">
                                        </span>
                                    </div>
                                    <div class="col-10">
                                        <table class="table">
                                            <tr>
                                                <td class="text-site-primary"><b>Product Name:</b></td>
                                                <td>Soudal Multi Purpose Silicone 270ml Clear</td>
                                            </tr>
                                            <tr>
                                                <td class="text-site-primary"><b>Category:</b></td>
                                                <td>Adhesives & Sealants</td>
                                            </tr>
                                            <tr>
                                                <td class="text-site-primary"><b>SKU:</b></td>
                                                <td>LS121644</td>
                                            </tr>
                                            <tr>
                                                <td class="text-site-primary"><b>QTY:</b></td>
                                                <td>3</td>
                                            </tr>
                                            <tr>
                                                <td class="text-site-primary"><b>I don't have this product!</b></td>
                                                <td>
                                                    <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal">
                                                        Search Alternative
                                                    </button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <!-- /Order Items -->
                            @endfor
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Single Order Content -->
        @endfor

        <div class="row">

            {{-- @foreach ($orders as $order)
            <div class="col-md-12 p-4 pr-4">
                <div class="card">
                    <div class="card-body p-2 pl-5 pr-5 pb-5">
                        <div class="p-2 mb-2">Order #{{1}} @if ($order->order_status == 'pending')
                            <a href="{{route('accept_order',['order_id'=>1])}}" class=" d-block btn btn-warning float-right">Click when preparing order</a>
                            <a href="{{route('cancel_order',['order_id'=>1])}}" onclick="cancelOrder(event)" class=" d-block btn btn-danger float-right" style="margin-right: 20px">Cancel Order</a>
                            @else
                            @if (!empty($order->delivery_boy_id))
                            <a href="" data-bs-toggle="modal" data-bs-target="#detailsModal{{1}}" class=" btn btn-primary d-block float-right">View Driver Details</a>
                            <?php
                            $user = \App\User::find($order->delivery_boy_id);
                            ?>
                            @if (!empty($user))
                            <div class="modal fade" id="detailsModal{{1}}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
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
                                            $fields = ['is_online', 'is_active', 'business_name', 'business_location', 'business_hours', 'bank_details', 'settings', 'user_img', 'remember_token', 'created_at', 'updated_at', 'pending_withdraw', 'total_withdraw', 'application_fee', 'temp_code'];
                                            ?>
                                            <div class="row">

                                                @foreach (json_decode($user) as $key => $u)
                                                @if (!empty($u) && !in_array($key, $fields))
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
                                                            @if ($user->is_active == 0)
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

                                @foreach ($order->items as $item)
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
            @endforeach --}}
        </div>

        <div class="row">
            <div class="col-md-12">
                {{-- {{ $orders_p->links() }} --}}
            </div>
        </div>

    </div>
    <!-- /Main Content -->

    {{-- Delivery Boy Details Modal --}}
    <?php
    // $user = \App\User::find($order->delivery_boy_id);
    $user = null;
    ?>
    @if (!empty($user))
        <div class="modal fade" id="detailsModal{{ 1 }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">{{ $user->name }} {{ $user->l_name }}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $fields = ['is_online', 'is_active', 'business_name', 'business_location', 'business_hours', 'bank_details', 'settings', 'user_img', 'remember_token', 'created_at', 'updated_at', 'pending_withdraw', 'total_withdraw', 'application_fee', 'temp_code'];
                        ?>
                        <div class="row">

                            @foreach (json_decode($user) as $key => $u)
                                @if (!empty($u) && !in_array($key, $fields))
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="" class="text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                                            <input type="text" disabled class="form-control" value="{{ $u }}">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="mt-5">
                                        @if ($user->is_active == 0)
                                            <a href="{{ route('change_user_status', ['user_id' => $user->id, 'status' => 1]) }}"> <span class="text-success">Click here to Enable Account</span></a>
                                        @else
                                            <a href="{{ route('change_user_status', ['user_id' => $user->id, 'status' => 0]) }}"> <span class="text-danger">Click here to Disable Account </span></a>
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
</div>
