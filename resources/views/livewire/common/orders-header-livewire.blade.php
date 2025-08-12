<div>

    @php
        use App\Enums\UserChoicesEnum;
        use App\Enums\OrderStatusEnum;
        use App\Enums\OrderTypeEnum;
        use App\Models\ProductsByBuyer;
        use App\Products;
        use App\Services\DateTimeServices;
    @endphp

    <x-session-messages />

    {{-- ************************************ Accept Order Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="acceptCustomProductOrderModal" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accept Order</h5>
                    <button type="button" class="close" wire:click="resetModal" aria-label="Close"
                        data-bs-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form wire:submit.prevent="customProductOrderIsAccepted" method="post">
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <label>Price By Seller</label>
                            <div class="form-group">
                                <input type="number" class="form-control" placeholder="Enter your price"
                                    wire:model.defer="priceBySeller"
                                    max="{{ $this->selectedOrder?->order_items[0]->product_price }}">
                            </div>
                            <small class="text-danger">
                                @error('priceBySeller')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Max Price By Buyer</label>
                            <div class="form-group">
                                <input type="text" class="form-control"
                                    value="${{ $selectedOrder?->order_items[0]->product_price }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetModal"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-site-primary" wire:target="customProductOrderIsAccepted"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                            wire:loading.attr="disabled">
                            <span wire:target="customProductOrderIsAccepted" wire:loading.remove>
                                Proceed
                            </span>
                            <span wire:target="customProductOrderIsAccepted" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status"
                                    aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ No Other Sellers Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="noOtherSellersModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="close" wire:click="resetModal" aria-label="Close"
                        data-bs-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form wire:submit.prevent="cancelOrder({{ $orderId }})" method="post">
                    <div class="modal-body">
                        <div class="text-center">
                            <h2>Attention!!</h2>
                            <div class="text-center">
                                <p>Sorry! There are no other sellers in this area except you</p>
                                <p>Do you want to cancel order #{{ $orderId }}?</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetModal"
                            data-bs-dismiss="modal">
                            No
                        </button>
                        <button type="submit" class="btn btn-danger" wire:target="cancelOrder"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger"
                            wire:loading.attr="disabled">
                            <span wire:target="cancelOrder" wire:loading.remove>
                                Yes
                            </span>
                            <span wire:target="cancelOrder" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status"
                                    aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Stuart Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="stuartModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="assignToStuartDriver">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title display-center">Add Custom Order Id</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="resetModal">
                            <span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Order #</label>
                                    <input type="text" wire:model.defer="customOrderId"
                                        placeholder="Enter custom order id or leave blank..." class="form-control"
                                        autofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer hidden">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-warning" wire:target="assignToStuartDriver"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning"
                            wire:loading.attr="disabled"
                            title="Automatically assign this order to a Stuart delivery boy">
                            <span wire:target="assignToStuartDriver" wire:loading.remove>
                                Assign
                            </span>
                            <span wire:target="assignToStuartDriver" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Gophr Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="gophrModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="assignToGophrDriver">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title display-center">Gophr Delivery</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="resetModal">
                            <span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Add Aditional Notes (Optional)</label>
                                    <input type="text" wire:model.defer="additionalParcelDescription"
                                        placeholder="Enter additional notes here if any..." class="form-control"
                                        autofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer hidden">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-site-primary" wire:target="assignToGophrDriver"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                            wire:loading.attr="disabled">
                            <span wire:target="assignToGophrDriver" wire:loading.remove>
                                Assign
                            </span>
                            <span wire:target="assignToGophrDriver" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Track Gophr Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="trackGophrDeliveryModal" tabindex="-1">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title display-center">Live Delivery Tracking</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if (isset($selectedDeliveryDetails))
                        <div class="row" style="height: 100vh;">
                            <iframe src="{{ $selectedDeliveryDetails['data']['deliveries'][0]['public_tracker_url'] }}"
                                class="col-12">
                            </iframe>
                        </div>
                    @endif
                </div>
                <div class="modal-footer hidden">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="resetModal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped table-responsive-sm">
        <thead>
            <tr>
                <div class="d-flex flex-column-reverse flex-md-row justify-content-between pb-4 gap-1">
                    <div>
                        @if ($order->order_status === OrderStatusEnum::PENDING->value)
                            @if ($order?->order_items[0]?->product_belongs_to_type === (new Products())->getMorphClass())
                                <button class="btn btn-success" wire:click="orderIsAccepted({{ $order->id }})"
                                    wire:target="orderIsAccepted({{ $order->id }})" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="btn-success" wire:loading.attr="disabled"
                                    title="Click here when preparing order">
                                    <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading.remove>
                                        Accept
                                    </span>
                                    <span wire:target="orderIsAccepted({{ $order->id }})" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
                                    </span>
                                </button>
                                <button class="btn btn-danger" wire:click="cancelOrder({{ $order->id }})"
                                    wire:target="cancelOrder({{ $order->id }})" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="btn-danger" wire:loading.attr="disabled"
                                    title="Cancel the whole order">
                                    <span wire:target="cancelOrder({{ $order->id }})" wire:loading.remove>
                                        Cancel
                                    </span>
                                    <span wire:target="cancelOrder({{ $order->id }})" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
                                    </span>
                                </button>
                            @endif

                            @if ($order->order_items[0]->product_belongs_to_type === (new ProductsByBuyer())->getMorphClass())
                                <button class="btn btn-success"
                                    wire:click="renderCustomProductOrderModal({{ $order->id }})"
                                    wire:loading.class="btn-dark" wire:loading.class.remove="btn-success"
                                    wire:loading.attr="disabled"
                                    wire:target="renderCustomProductOrderModal({{ $order->id }})"
                                    title="Accept the order">
                                    <span wire:target="renderCustomProductOrderModal({{ $order->id }})"
                                        wire:loading.remove>
                                        Accept
                                    </span>
                                    <span wire:target="renderCustomProductOrderModal({{ $order->id }})"
                                        wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status"
                                            aria-hidden="true"></span>
                                    </span>
                                </button>
                                <button class="btn btn-danger"
                                    wire:click="sendCustomProductOrderToAnOtherSeller({{ $order->id }})"
                                    wire:target="sendCustomProductOrderToAnOtherSeller({{ $order->id }})"
                                    wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger"
                                    wire:loading.attr="disabled" title="Send this order to another nearby seller">
                                    <span wire:target="sendCustomProductOrderToAnOtherSeller({{ $order->id }})"
                                        wire:loading.remove>
                                        Send To Other Sellers
                                    </span>
                                    <span wire:target="sendCustomProductOrderToAnOtherSeller({{ $order->id }})"
                                        wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status"
                                            aria-hidden="true"></span>
                                    </span>
                                </button>
                            @endif
                        @endif

                        @if ($order->type === OrderTypeEnum::DELIVERY->value)
                            @if ($order->order_status === OrderStatusEnum::ACCEPTED->value)
                                <!-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#stuartModal" wire:click="renderStuartModal({{ $order->id }})" wire:target="renderStuartModal({{ $order->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Assign this order to Stuart delivery boy">
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading.remove>
                                                            Assign To Stuart Delivery
                                                        </span>
                                                        <span wire:target="renderStuartModal({{ $order->id }})" wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                                                        </span>
                                                    </button> -->
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#gophrModal" wire:click="renderOrderId({{ $order->id }})"
                                    wire:target="renderOrderId({{ $order->id }})" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="btn-warning" wire:loading.attr="disabled"
                                    title="Assign this order to delivery boy">
                                    <span wire:target="renderOrderId({{ $order->id }})" wire:loading.remove>
                                        Assign To Delivery Boy
                                    </span>
                                    <span wire:target="renderOrderId({{ $order->id }})" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
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
                                <button class="btn btn-dark" data-bs-toggle="modal"
                                    data-bs-target="#trackGophrDeliveryModal"
                                    wire:click="renderTrackGophrDeliveryModal({{ $order->id }})"
                                    wire:target="renderTrackGophrDeliveryModal({{ $order->id }})"
                                    wire:loading.class="btn-dark" wire:loading.class.remove="btn-dark"
                                    wire:loading.attr="disabled" title="Track the live status of your delivery">
                                    <span wire:target="renderTrackGophrDeliveryModal({{ $order->id }})"
                                        wire:loading.remove>
                                        Track Delivery
                                    </span>
                                    <span wire:target="renderTrackGophrDeliveryModal({{ $order->id }})"
                                        wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
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
                                        This is a <b>{{ $order->type }}</b> order therefore only press the <b>Complete
                                            Order</b> button when the customer has collected the order physically
                                    </p>
                                </div>
                                <button class="btn btn-success" wire:click="orderIsCompleted({{ $order->id }})"
                                    wire:target="orderIsCompleted({{ $order->id }})" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="btn-success" wire:loading.attr="disabled"
                                    title="Mark as completed">
                                    <span wire:target="orderIsCompleted({{ $order->id }})" wire:loading.remove>
                                        Complete Order
                                    </span>
                                    <span wire:target="orderIsCompleted({{ $order->id }})" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
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
                    </div>

                    <div>
                        @if ($order->order_items[0]->product_belongs_to_type == (new ProductsByBuyer())->getMorphClass())
                            <button type="button" class="btn btn-primary"
                                title="This is a custom product order created by the buyer. You may not uploaded it into our system but if you have it in your physical warehouse then you can accept this order happily & make money 😉">
                                <i class="fas fa-fingerprint"></i>
                                Custom Order
                            </button>
                        @endif
                    </div>
                </div>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed text-site-primary" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $order->id }}"
                                    aria-expanded="false" aria-controls="collapse{{ $order->id }}">
                                    <h5>Customer Details</h5>
                                </button>
                            </h2>
                            <div id="collapse{{ $order->id }}" class="accordion-collapse collapse"
                                aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <div class="">
                                        <table class="table table-striped table-responsive-sm">
                                            <tr>
                                                <td><b>Name</b></td>
                                                <td>{{ $order->customer_name }}</td>
                                                <td><b>Contact</b></td>
                                                <td>{{ $order->phone_number }}</td>
                                            </tr>
                                            <tr>
                                                <td><b>Address</b></td>
                                                <td colspan="3">{{ $order->address }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="8">
                    <h5 class="text-site-primary">Order Details</h5>
                </th>
            </tr>

            <tr>
                <td><b>Order#</b></td>
                <td>{{ $order->id }}</td>
                <td><b>Order Total</b></td>
                <td>£{{ $order->current_total }}</td>
                <td><b>Date</b></td>
                <td>{{ DateTimeServices::getDateOnly($order->created_at) }}</td>
                <td><b>Time</b></td>
                <td>{{ DateTimeServices::getTimeOnlyWithOutSeconds($order->created_at) }}</td>
                {{-- <td><b>Order Status</b></td>
                <td><span class="badge badge-warning">{{ $order->order_status }}</span></td> --}}
            </tr>

            <tr>
                {{-- <td><b>Placed At</b></td>
                <td>{{ $order->created_at }}</td> --}}
                <td><b>Order Type</b></td>
                <td><span class="badge badge-info">{{ $order->type }}</span></td>
                <td><b>Order Status</b></td>
                <td><span class="badge badge-warning">{{ $order->order_status }}</span></td>
                <td colspan="2"><b>Payment Status</b></td>
                <td colspan="2"><span class="badge badge-primary">{{ $order->payment_status }}</span></td>
            </tr>

            <tr>
                {{-- <td><b>Order Total</b></td>
                <td>£{{ $order->current_total }}</td> --}}
                {{-- <td><b>Payment Status</b></td>
                <td><span class="badge badge-primary">{{ $order->payment_status }}</span></td> --}}
            </tr>

            {{-- <tr>
                <th colspan="4">
                    <h5>Customer Details</h5>
                </th>
            </tr>

            <tr>
                <td><b>Name</b></td>
                <td>{{ $order->customer_name }}</td>
                <td><b>Contact</b></td>
                <td>{{ $order->phone_number }}</td>
            </tr>
            <tr>
                <td><b>Address</b></td>
                <td colspan="3">{{ $order->address }}</td>
            </tr> --}}

            <tr>
                <th colspan="8">
                    <h5 class="text-site-primary">Order Items</h5>
                </th>
            </tr>
        </tbody>
    </table>

</div>
