<div class="container-xxl flex-grow-1 container-p-y">

    @php
    use App\Enums\UserChoicesEnum;
    use App\Enums\OrderStatusEnum;
    use App\Enums\OrderTypeEnum;
    @endphp

    <x-session-messages />

    {{-- ************************************ Search Alternative Product Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="searchAlternativeProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Alternative Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    @if (empty($orderId) || empty($currentProdId) || empty($currentProdQty) || empty($customerName) || empty($phoneNumber))
                    <div class="col-12 text-center">
                        <div class="spinner-border" role="status"></div>
                    </div>
                    @else
                    <livewire:sellers.modals.search-alternative-product-modal
                        :order_id="$orderId"
                        :current_prod_id="$currentProdId"
                        :current_prod_qty="$currentProdQty"
                        :receiver_name="$customerName"
                        :phone_number="$phoneNumber">
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
                        <h2>Please select a store</h2>
                        <div class="d-flex justify-content-center">
                            @if (empty($nearbySellers))
                            <div class="col-6">
                                <div class="spinner-border" role="status"></div>
                            </div>
                            @else
                            <div class="col-6">
                                <select class="form-select form-select-lg" wire:model="selectedNearbySeller">
                                    <option value="" selected>Nearby stores</option>
                                    @foreach ($nearbySellers as $singleIndex)
                                    <option value="{{ $singleIndex['business_name'] }}">{{ $singleIndex['business_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                        <small class="text-danger">
                            @error('selectedNearbySeller')
                            {{ $message }}
                            @enderror
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
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
                                    <input type="text" wire:model.defer="customOrderId" placeholder="Enter custom order id or leave blank..." class="form-control" autofocus>
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
    {{-- ************************************ Gophr Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="gophrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="assignToGophrDriver">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title display-center">Gophr Delivery</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Add Aditional Notes (Optional)</label>
                                    <input type="text" wire:model.defer="additionalParcelDescription" placeholder="Enter additional notes here if any..." class="form-control" autofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer hidden">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-warning" wire:target="assignToGophrDriver" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                            <span wire:target="assignToGophrDriver" wire:loading.remove>
                                Assign
                            </span>
                            <span wire:target="assignToGophrDriver" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Track Gophr Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="trackGophrDeliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="assignToGophrDriver">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title display-center">Live Delivery Tracking</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if (isset($selectedDeliveryDetails))
                        <div class="row" style="height: 100vh;">
                            <iframe
                                src="{{ $selectedDeliveryDetails['data']['deliveries'][0]['public_tracker_url'] }}"
                                class="col-12">
                            </iframe>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer hidden">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetModal">
                            Close
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
                                        @if ($order->order_status === OrderStatusEnum::PENDING->value)
                                        <button class="btn btn-warning" wire:click="orderIsAccepted({{ $order->id }})" wire:target="orderIsAccepted({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Click here when preparing order">
                                            <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading.remove>
                                                Accept
                                            </span>
                                            <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading>
                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>

                                        <button class="btn btn-danger" wire:click="cancelOrder({{ $order->id }})" wire:target="cancelOrder({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled" title="Cancel the whole order">
                                            <span wire:target="cancelOrder({{ $order->id }})" wire:loading.remove>
                                                Cancel
                                            </span>
                                            <span wire:target="cancelOrder({{ $order->id }})" wire:loading>
                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        @endif

                                        @if ($order->type === OrderTypeEnum::DELIVERY->value)
                                        @if ($order->order_status === OrderStatusEnum::ACCEPTED->value)
                                        <!-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#stuartModal" wire:click="renderStuartModal({{ $order->id }})" wire:target="renderStuartModal({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Assign this order to Stuart delivery boy">
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading.remove>
                                                            Assign To Stuart Delivery
                                                        </span>
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </button> -->
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#gophrModal" wire:click="renderOrderId({{ $order->id }})" wire:target="renderOrderId({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Assign this order to delivery boy">
                                            <span wire:target="renderOrderId({{ $order->id }})" wire:loading.remove>
                                                Assign To Delivery Boy
                                            </span>
                                            <span wire:target="renderOrderId({{ $order->id }})" wire:loading>
                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        @endif

                                        @if ($order->order_status === OrderStatusEnum::STUART_DELIVERY->value)
                                        <div class="alert alert-primary" role="alert">
                                            <p>
                                                Stuart delivery is on the way..!!
                                            </p>
                                        </div>
                                        @endif

                                        @if ($order->order_status === OrderStatusEnum::ON_THE_WAY->value)
                                        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#trackGophrDeliveryModal" wire:click="renderTrackGophrDeliveryModal({{ $order->id }})" wire:target="renderTrackGophrDeliveryModal({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-dark" wire:loading.attr="disabled" title="Track the live status of your delivery">
                                            <span wire:target="renderTrackGophrDeliveryModal({{ $order->id }})" wire:loading.remove>
                                                Track Delivery
                                            </span>
                                            <span wire:target="renderTrackGophrDeliveryModal({{ $order->id }})" wire:loading>
                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        @endif
                                        @endif

                                        @if ($order->type === OrderTypeEnum::SELF_PICKUP->value)
                                        @if ($order->order_status === OrderStatusEnum::ACCEPTED->value)
                                        <div class="alert alert-primary" role="alert">
                                            <p>
                                                A {{ $order->type }} email has been sent to the customer
                                            </p>
                                            <hr>
                                            <h4 class="alert-heading">IMPORTANT NOTE!</h4>
                                            <p class="mb-0">
                                                This is a <b>{{ $order->type }}</b> order therefore only press the <b>Complete Order</b> button when the customer has collected the order physically
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

                                        @if ($order->order_status == OrderStatusEnum::COMPLETE->value)
                                        <button class="btn btn-success" disabled title="This order has been completed">
                                            Order Completed
                                        </button>
                                        @endif

                                        @if ($order->order_status == OrderStatusEnum::CANCELLED->value)
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
                                    <td>£{{ $order->initial_total }}</td>
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
                                        <td class="col-4 text-site-primary"><b>Product Name</b></td>
                                        <td class="col-8">{{ $item->product_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Category</b></td>
                                        <td class="col-8">{{ $item->category->category_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>SKU</b></td>
                                        <td class="col-8">{{ $item->sku }}</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>QTY</b></td>
                                        <td class="col-8"> {{ $order->order_items[$index]->product_qty }} </td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Price</b></td>
                                        <td class="col-8"> £{{ $item->price }} </td>
                                    </tr>
                                    @if ($order->order_status == OrderStatusEnum::PENDING->value)
                                    <tr>
                                        <td class="col-4 text-site-primary">
                                            <b>I don't have this product!</b>
                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="If you don't have this product then go for the option selected by the buyer by clicking the front button">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </button>
                                        </td>
                                        <td class="col-8">
                                            @if ($order->order_items[$index]->user_choice === UserChoicesEnum::ALTERNATIVE_PRODUCT->value)
                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal" wire:click="renderSAPModal({{ $order->id }}, {{ $item->id }}, {{ $order->order_items[$index]->product_qty }}, '{{ $order->customer_name }}', '{{ $order->phone_number }}')">
                                                <i class="fas fa-search"></i>
                                                Search Alternative
                                            </button>
                                            @elseif ($order->order_items[$index]->user_choice === UserChoicesEnum::REMOVE_PRODUCT->value)
                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#removeItemFromOrderModel" wire:click="renderRemoveItemModal({{ $order->order_items[$index] }})">
                                                <i class="fas fa-minus-circle"></i>
                                                Remove Product
                                            </button>
                                            @elseif ($order->order_items[$index]->user_choice === UserChoicesEnum::SEND_TO_OTHER_STORES->value)
                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#sendToOtherStoresModal" wire:click="renderSTOSModal({{ $order->id }})">
                                                <i class="fas fa-paper-plane"></i>
                                                Send To Other Stores
                                            </button>
                                            @elseif ($order->order_items[$index]->user_choice === UserChoicesEnum::CALL_ME->value)
                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#showCustomerContactModel" wire:click="renderCustomerContactModal('{{ $order->receiver_name }}', '{{ $order->phone_number }}')">
                                                <i class="fas fa-phone-alt"></i>
                                                Call The Customer
                                            </button>
                                            @elseif ($order->order_items[$index]->user_choice === UserChoicesEnum::CANCEL_ORDER->value)
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
        <p class="fs-1">No orders yet... :(</p>
        @endforelse
    </div>

    @if (!empty($data))
    <div class="row">
        <div class="col-md-12">
            {{ $data->links() }}
        </div>
    </div>
    @endif
    <!-- /Main Content -->
</div>