<div class="container-xxl flex-grow-1 container-p-y">
    <div class="container pt-4">
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                {{ session()->get('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
    {{-- ************************************ Delivery Boy Details Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="deliveryBoyDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Boy Details</h5>
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
    {{-- ************************************ Search Alternative Product Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="searchAlternativeProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Alternative Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    @if (empty($order_id) || empty($current_prod_id) || empty($current_prod_qty) || empty($receiver_name) || empty($phone_number))
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    @else
                        <livewire:sellers.modals.search-alternative-product-modal :order_id="$order_id" :current_prod_id="$current_prod_id" :current_prod_qty="$current_prod_qty" :receiver_name="$receiver_name" :phone_number="$phone_number">
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ************************************ Remove Product From Order Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="removeItemFromOrderModel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove This Item From The Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h2 class="text-danger">WARNING!</h2>
                        <p>Are you sure that you want to remove this product from the order??</p>
                        <p class="fw-bold">You can't undo this action</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-site-primary" wire:click="removeItemFromOrder" wire:target="removeItemFromOrder" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="removeItemFromOrder" wire:loading.remove>
                            Confirm
                        </span>
                        <span wire:target="removeItemFromOrder" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ************************************ Search From Other Stores Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="sendToOtherStoresModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send This Item To Other Stores</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h2>PLEASE SELECT A STORE</h2>
                        <div class="d-flex justify-content-center">
                            @if (empty($nearby_sellers))
                                <div class="col-6">
                                    <div class="spinner-border" role="status"></div>
                                </div>
                            @else
                                <div class="col-6 border border-danger">
                                    <select class="form-select form-select-lg" wire:model="selected_nearby_seller">
                                        <option value="" selected>Stores list</option>
                                        @foreach ($nearby_sellers as $single_index)
                                            <option value="{{ $single_index['business_name'] }}">{{ $single_index['business_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <small class="text-danger">
                            @error('selected_nearby_seller')
                                {{ $message }}
                            @enderror
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-site-primary" wire:click="removeItemFromOrder" wire:target="removeItemFromOrder" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="removeItemFromOrder" wire:loading.remove>
                            Send
                        </span>
                        <span wire:target="removeItemFromOrder" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                        </span>
                    </button> --}}
                    <button type="button" class="btn btn-site-primary" wire:click="sendItemToAnOtherStore" wire:target="sendItemToAnOtherStore" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="sendItemToAnOtherStore" wire:loading.remove>
                            Send
                        </span>
                        <span wire:target="sendItemToAnOtherStore" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ************************************ Show Customer Contact Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="showCustomerContactModel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModelLabel">Customer Contact Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    @if (empty($receiver_name) || empty($phone_number))
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    @else
                        <div class="text-center">
                            <h2 class="text-danger"><i class="fas fa-phone-alt"></i> CALL THE CUSTOMER</h2>
                            <p class="fw-bold">Customer Name: {{ $receiver_name }}</p>
                            <p class="fw-bold">Customer Contact: {{ $phone_number }}</p>
                        </div>
                        <div class=" d-flex justify-content-center">
                            <div class="d-flex justify-content-center col-8 col-sm-6">
                                <select class="form-select" aria-label="Default select example">
                                    <option value="1">Search Alternative</option>
                                    <option value="2">Remove Product</option>
                                    <option value="3">Cancel</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-site-primary" wire:click="" wire:target="" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="" wire:loading.remove="">
                            Select
                        </span>
                        <span wire:target="" wire:loading="">
                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ************************************ Stuart Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="stuartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="assignToStuartDriver">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title display-center">Add Custom Order Id</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Order #</label>
                                    <input type="text" wire:model.defer="custom_order_id" placeholder="Enter custom order id or leave blank..." class="form-control" autofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer hidden">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-warning" wire:target="assignToStuartDriver" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Automatically assign this order to a Stuart delivery boy">
                            <span wire:target="assignToStuartDriver" wire:loading.remove>
                                Assign
                            </span>
                            <span wire:target="assignToStuartDriver" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Content Header -->
    <form wire:submit.prevent="render">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-8 py-4 my-2">
                <input type="number" wire:model.defer="search" class="form-control" placeholder="Search by order#">
            </div>
            <div class="col-12 col-sm-12 col-md-4 d-flex">
                <button type="submit" class="btn btn-site-primary my-4 p-1 w-100 mx-1" wire:target="search" wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled" title="Search">
                    <span class='fas fa-search' wire:target="search" wire:loading.remove></span>
                    <span wire:target="search" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-primary my-4 p-1 w-100 mx-1" wire:click="resetThisPage" wire:target="resetThisPage" wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary" wire:loading.attr="disabled" title="Reset orders page">
                    <span class="fas fa-sync" wire:target="resetThisPage" wire:loading.remove></span>
                    <span wire:target="resetThisPage" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                    </span>
                </button>
            </div>
        </div>
    </form>
    <!-- /Content Header -->

    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1">Orders</h4>
        </div>
        @forelse ($data as $order)
            <!-- Single Order Content -->
            <div class="col-12 p-2">
                <div class="card">
                    <div class="card-body py-1 px-2">
                        <!-- Order Header -->
                        <div class="p-2 mb-2">
                            <table class="table table-striped table-responsive-sm">
                                <thead>
                                    <tr>
                                        <td colspan="4">
                                            @if ($order->order_status === 'pending')
                                                <button class="btn btn-warning" wire:click="orderIsAccepted({{ $order->id }})" wire:target="orderIsAccepted({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Click here when preparing order">
                                                    <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading.remove>
                                                        Accept Order
                                                    </span>
                                                    <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading>
                                                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>

                                                <button class="btn btn-danger" wire:click="cancelOrder({{ $order }})" wire:target="cancelOrder({{ $order }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled" title="Cancel the whole order">
                                                    <span wire:target="cancelOrder({{ $order }})" wire:loading.remove>
                                                        Cancel Order
                                                    </span>
                                                    <span wire:target="cancelOrder({{ $order }})" wire:loading>
                                                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>
                                            @endif

                                            @if ($order->type === 'delivery')
                                                @if ($order->order_status === 'accepted')
                                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#stuartModal" wire:click="renderStuartModal({{ $order->id }})" wire:target="renderStuartModal({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Assign this order to Stuart delivery boy">
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading.remove>
                                                            Assign To Stuart Delivery
                                                        </span>
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </button>
                                                @endif

                                                @if ($order->order_status === 'stuartDelivery')
                                                    <div class="alert alert-primary" role="alert">
                                                        <p>
                                                            Stuart delivery is on the way..!!
                                                        </p>
                                                    </div>
                                                @endif

                                                @if ($order->delivery_boy_id != '')
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliveryBoyDetailsModal" title="View delivery boy details">
                                                        Delivery Boy Details
                                                    </button>
                                                @endif
                                            @endif

                                            @if ($order->type === 'self-pickup')
                                                @if ($order->order_status === 'accepted')
                                                    <div class="alert alert-primary" role="alert">
                                                        <p>
                                                            A {{ $order->type }} email has been sent to the customer
                                                        </p>
                                                        <hr>
                                                        <h4 class="alert-heading">IMPORTANT NOTE!</h4>
                                                        <p class="mb-0">
                                                            This is a <b>{{ $order->type }}</b> order therefore only press the <b>complete button</b> when the customer has collected the order
                                                        </p>
                                                    </div>
                                                    <button class="btn btn-success" wire:click="orderIsCompleted({{ $order->id }})" wire:target="orderIsCompleted({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Mark as completed">
                                                        <span wire:target="orderIsCompleted({{ $order->id }})" wire:loading.remove>
                                                            Complete Order
                                                        </span>
                                                        <span wire:target="orderIsCompleted({{ $order->id }})" wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </button>
                                                @endif
                                            @endif

                                            @if ($order->order_status == 'complete')
                                                <button class="btn btn-success" disabled title="This order has been completed">
                                                    Order Completed
                                                </button>
                                            @endif

                                            @if ($order->order_status == 'cancelled')
                                                <button class="btn btn-dark" disabled title="This order has been cencelled">
                                                    Order Cancelled
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Order#</b></td>
                                        <td>{{ $order->id }}</td>
                                        <td><b>Order Status</b></td>
                                        <td><span class="badge badge-warning">{{ $order->order_status }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Placed At</b></td>
                                        <td>{{ $order->created_at }}</td>
                                        <td><b>Order Type</b></td>
                                        <td><span class="badge badge-info">{{ $order->type }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Order Total</b></td>
                                        <td>£{{ $order->order_total }}</td>
                                        <td><b>Payment Status</b></td>
                                        <td><span class="badge badge-primary">{{ $order->payment_status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /Order Header -->
                        <div class="card-text">
                            @foreach ($order->products as $index => $item)
                                <!-- Order Items -->
                                <div class="row mb-2">
                                    <div class="col-md-2">
                                        <span class="img-container">
                                            @if (str_contains($item->feature_img, 'https://'))
                                                <img class="d-block m-auto" src="{{ asset($item->feature_img) }}">
                                            @else
                                                <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $item->feature_img }}">
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-12 col-sm-10">
                                        <table class="table">
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Product Id: (Remove in production)</b></td>
                                                <td class="col-8">{{ $item->id }}</td>
                                            </tr>
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Product Name:</b></td>
                                                <td class="col-8">{{ $item->product_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Category:</b></td>
                                                <td class="col-8">{{ $item->category->category_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>SKU:</b></td>
                                                <td class="col-8">{{ $item->sku }}</td>
                                            </tr>
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>QTY:</b></td>
                                                <td class="col-8"> {{ $order->order_items[$index]->product_qty }} </td>
                                            </tr>
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Price:</b></td>
                                                <td class="col-8"> £{{ $item->price }} </td>
                                            </tr>
                                            @if ($order->order_status == 'pending')
                                                <tr>
                                                    <td class="col-4 text-site-primary">
                                                        <b>I don't have this product!</b>
                                                        <button type="button" class="btn btn-site-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="If you don't have this product then go for the option selected by the user by clicking the front button.">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                        </button>
                                                    </td>
                                                    <td class="col-8">
                                                        @if ($order->order_items[$index]->user_choice === 1)
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal" wire:click="renderSAPModal({{ $order->id }}, {{ $item->id }}, {{ $order->order_items[$index]->product_qty }}, '{{ $order->receiver_name }}', '{{ $order->phone_number }}')">
                                                                <i class="fas fa-search"></i>
                                                                Search Alternative
                                                            </button>
                                                        @elseif ($order->order_items[$index]->user_choice === 2)
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#removeItemFromOrderModel" wire:click="renderRemoveItemModal({{ $order->order_items[$index] }})">
                                                                <i class="fas fa-minus-circle"></i>
                                                                Remove Product
                                                            </button>
                                                        @elseif ($order->order_items[$index]->user_choice === 3)
                                                            {{-- <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#sendToOtherStoresModal" wire:click="renderSTOSModal({{ $order }}, {{ $order->order_items[$index] }})">
                                                                <i class="fas fa-paper-plane"></i>
                                                                Send To Other Stores
                                                            </button> --}}
                                                            <button type="button" class="btn btn-site-primary" disabled>
                                                                <i class="fas fa-paper-plane"></i>
                                                                Send To Other Stores (Feature Underdevelopment)
                                                            </button>
                                                        @elseif ($order->order_items[$index]->user_choice === 4)
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#showCustomerContactModel" wire:click="renderCustomerContactModal('{{ $order->receiver_name }}', '{{ $order->phone_number }}')">
                                                                <i class="fas fa-phone-alt"></i>
                                                                Call The Customer
                                                            </button>
                                                        @elseif ($order->order_items[$index]->user_choice === 5)
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal" wire:click="renderSAPModal({{ $order->id }}, {{ $item->id }}, {{ $order->order_items[$index]->product_qty }}, '{{ $order->receiver_name }}', '{{ $order->phone_number }}')">
                                                                <i class="fas fa-times"></i>
                                                                Cancel Order
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                <!-- /Order Items -->
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Single Order Content -->
        @empty
            <h1>No orders yet... :(</h1>
        @endforelse


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
                                                        <label  class="text-capitalize">{{str_replace('_',' ',$key)}}</label>
                                                        <input type="text" disabled class="form-control" value="{{$u}}">
                                                    </div>
                                                </div>
                                                @endif
                                                @endforeach
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label  class="mt-5">
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

        @if (!empty($data))
            <div class="row">
                <div class="col-md-12">
                    {{ $data->links() }}
                </div>
            </div>
        @endif

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
                                            <label class="text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                                            <input type="text" disabled class="form-control" value="{{ $u }}">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="mt-5">
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
    {{-- @endif --}}
</div>
