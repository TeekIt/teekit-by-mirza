<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Accept Order Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="acceptOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accept Order</h5>
                    <button type="button" class="close" wire:click="resetModal" aria-label="Close" data-bs-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form wire:submit.prevent="acceptedBySeller" method="post">
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <label>Price By Seller</label>
                            <div class="form-group">
                                <input type="number" class="form-control" placeholder="Enter your price" wire:model.defer="priceBySeller" max="{{ $this->selectedOrder?->order_items[0]->product_price }}">
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
                                <input type="text" class="form-control" value="${{ $this->selectedOrder?->order_items[0]->product_price }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetModal" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-site-primary" wire:target="acceptedBySeller" wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled">
                            <span wire:target="acceptedBySeller" wire:loading.remove>
                                Proceed
                            </span>
                            <span wire:target="acceptedBySeller" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
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
                    <button type="button" class="close" wire:click="resetModal" aria-label="Close" data-bs-dismiss="modal">
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
                        <button type="button" class="btn btn-secondary" wire:click="resetModal" data-bs-dismiss="modal">
                            No
                        </button>
                        <button type="submit" class="btn btn-danger" wire:target="cancelOrder" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled">
                            <span wire:target="cancelOrder" wire:loading.remove>
                                Yes
                            </span>
                            <span wire:target="cancelOrder" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1 text-site-primary">Orders Of Unique Products</h4>
        </div>
        @forelse ($data as $singleIndex)
            <!-- Single Order Content -->
            <div class="col-12 p-2">
                <div class="card">
                    <div class="card-body py-1 px-2">
                        <!-- Order Header -->
                        <div class="p-2 mb-2">
                            <table class="table table-striped table-responsive-sm">
                                <thead>
                                    <tr class="col-12">
                                        <td colspan="6">
                                            <div class="row">
                                                <div class="col-12 col-md-10">
                                                    @if ($singleIndex->order_status === 'pending')
                                                        <button class="btn btn-success col-12 col-md-2 m-1 m-md-0"  wire:click="renderAcceptOrderModal({{ $singleIndex->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" wire:target="renderAcceptOrderModal({{ $singleIndex->id }})" title="Accept the order">
                                                            <span wire:target="renderAcceptOrderModal({{ $singleIndex->id }})" wire:loading.remove>
                                                                Accept
                                                            </span>
                                                            <span wire:target="renderAcceptOrderModal({{ $singleIndex->id }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>

                                                        <button class="btn btn-danger col-12 col-md-5 m-1 m-md-0"  wire:click="sendItemToAnOtherSeller({{ $singleIndex->id }})" wire:target="sendItemToAnOtherSeller({{ $singleIndex->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled" title="Send this order to another nearby seller">
                                                            <span wire:target="sendItemToAnOtherSeller({{ $singleIndex->id }})" wire:loading.remove>
                                                                Send To Other Sellers
                                                            </span>
                                                            <span wire:target="sendItemToAnOtherSeller({{ $singleIndex->id }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($singleIndex->order_status === 'accepted')
                                                        <button class="btn btn-warning col-12 col-md-2 m-1 m-md-0" wire:click="readyBySeller({{ $singleIndex->id }}, '{{ $singleIndex->type }}')" wire:target="readyBySeller({{ $singleIndex->id }}, '{{ $singleIndex->type }}')" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Mark the order as ready">
                                                            <span wire:target="readyBySeller({{ $singleIndex->id }}, '{{ $singleIndex->type }}')" wire:loading.remove>
                                                                Ready
                                                            </span>
                                                            <span wire:target="readyBySeller({{ $singleIndex->id }}, '{{ $singleIndex->type }}')" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($singleIndex->order_status === 'ready')
                                                        <button class="btn btn-warning col-12 col-md-2 m-1 m-md-0" wire:click="deliveredBySeller({{ $singleIndex->id }})" wire:target="deliveredBySeller({{ $singleIndex->id }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Mark the order as delivered">
                                                            <span wire:target="deliveredBySeller({{ $singleIndex->id }})" wire:loading.remove>
                                                                Deliver
                                                            </span>
                                                            <span wire:target="deliveredBySeller({{ $singleIndex->id }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($singleIndex->order_status === 'onTheWay')
                                                        <button class="btn btn-dark col-12 col-md-2 m-1 m-md-0" title="Our delivery boy is delivering your order">
                                                            On The Way 😊
                                                        </button>
                                                    @endif

                                                    @if ($singleIndex->order_status === 'delivered')
                                                        <button class="btn btn-dark col-12 col-md-2 m-1 m-md-0" title="You have delivered the order successfully">
                                                            Delivered 🥳
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Order#</b></td>
                                        <td>{{ $singleIndex->id }}</td>
                                        <td><b>Order Status</b></td>
                                        <td><span class="badge badge-warning">{{ $singleIndex->order_status }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Placed At</b></td>
                                        <td>{{ $singleIndex->created_at }}</td>
                                        <td><b>Order Type</b></td>
                                        <td><span class="badge badge-info">{{ $singleIndex->type }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Order Total</b></td>
                                        <td>£{{ $singleIndex->current_total }}</td>
                                        <td><b>Payment Status</b></td>
                                        <td><span class="badge badge-primary">{{ $singleIndex->payment_status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /Order Header -->
                        <div class="card-text">
                            <!-- Order Items -->
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <span class="img-container">
                                        @if (str_contains($singleIndex->order_items[0]->product?->feature_img, 'https://'))
                                            <img class="d-block m-auto" src="{{ $singleIndex->order_items[0]->product?->feature_img }}">
                                        @else
                                            <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $singleIndex->order_items[0]->product?->feature_img }}">
                                        @endif
                                    </span>
                                </div>
                                <div class="col-12 col-sm-10">
                                    <table class="table">
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Name:</b></td>
                                            <td class="col-8">{{ $singleIndex->order_items[0]->product?->product_name }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td class="col-4 text-site-primary"><b>Category:</b></td>
                                            <td class="col-8">{{ $singleIndex->order_items[0]->product?->category->category_name }}</td>
                                        </tr> --}}
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>QTY:</b></td>
                                            <td class="col-8">{{ $singleIndex->order_items[0]->product_qty }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Max Price:</b></td>
                                            <td class="col-8">£{{ $singleIndex->order_items[0]->product_price }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <!-- /Order Items -->

                        </div>

                    </div>
                </div>
            </div>
            <!-- /Single Order Content -->
        @empty
            <h2>No orders of any unique products yet... 🥺</h2>
        @endforelse

        @if (!empty($data))
            <div class="row">
                <div class="col-md-12">
                    {{ $data->links() }}
                </div>
            </div>
        @endif

    </div>
    <script>
        class TimerManager {
            constructor(initialMinutes, initialSeconds, holdingMinutes = 0, holdingSeconds = 0) {

                this.initialMinutes = initialMinutes;
                this.initialSeconds = initialSeconds;

                this.holdingMinutes = holdingMinutes;
                this.holdingSeconds = holdingSeconds;

            }

            holdThisTimer = (id) => {
                alert("holder called");
                localStorage.setItem(id, this.holdingMinutes + ':' + this.holdingSeconds);
            }

            padZero = (num) => {
                return (num < 10 ? '0' : '') + num;
            }

            setLocalStorage = (key, value) => {
                localStorage.setItem(key, value);
            }

            getTimerElements = () => {
                return document.querySelectorAll('.timer');
            }

            updateTimers = () => {
                let timerElements = this.getTimerElements();

                timerElements.forEach(timerElement => {
                    let timerElementId = timerElement.getAttribute('id');
                    let localStorageValue = localStorage.getItem(timerElementId);
                    let minutes, seconds;

                    if (localStorageValue != null) {
                        [minutes, seconds] = localStorageValue.split(':').map(Number);
                    } else {
                        minutes = this.initialMinutes;
                        seconds = this.initialSeconds;
                    }

                    seconds--;

                    if (seconds < 0) {
                        seconds = 59;
                        minutes--;
                    }

                    if (minutes > 0 || (minutes === 0 && seconds > 0)) {
                        let digitalTime = this.padZero(minutes) + ':' + this.padZero(seconds);
                        timerElement.textContent = digitalTime;
                        this.setLocalStorage(timerElementId, `${minutes}:${seconds}`);
                    } else {
                        timerElement.textContent = '00:00';
                        this.setLocalStorage(timerElementId, '00:00');
                    }
                });
            }

            start = () => {
                this.updateTimers();
                setInterval(() => this.updateTimers(), 1000);

                document.addEventListener('DOMContentLoaded', () => {
                    let timerElements = this.getTimerElements();
                    timerElements.forEach(timerElement => {
                        let timerKey = timerElement.getAttribute('id');
                        let timerValue = localStorage.getItem(timerKey);
                        if (timerValue !== null) {
                            timerElement.textContent = timerValue;
                        }
                    });
                });
            }
        }

        /* Params: minutes, seconds, holdingMinutes, holdingSeconds */
        const timerManager = new TimerManager({{ $orderHoldingMinutes }}, '00', '5', '59');
        timerManager.start();
    </script>

</div>
