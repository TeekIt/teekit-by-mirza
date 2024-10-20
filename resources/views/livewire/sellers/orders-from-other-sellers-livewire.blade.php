<div class="container-xxl flex-grow-1 container-p-y">
    
    <x-session-messages />

    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1">Orders From Other Sellers</h4>
        </div>
        @forelse ($data as $order_from_other_seller)
            <!-- Single Order Content -->
            <div class="col-12 p-2" wire:poll.60000ms="moveToAnotherSeller({{ $order_from_other_seller->id }}, '{{ $order_from_other_seller->order_status }}', {{ $order_from_other_seller->customer_lat }}, {{ $order_from_other_seller->customer_lon }}, '{{ $order_from_other_seller->created_at }}')">
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
                                                    {{-- <button class="btn btn-warning col-3 col-md-2" title="Hold the order">
                                                        <span onclick="timerManager.holdThisTimer({{ $order_from_other_seller->id }})">
                                                            Hold
                                                        </span>
                                                        <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span>
                                                    </button> --}}

                                                    @if ($order_from_other_seller->order_status === 'pending')
                                                        <button class="btn btn-success col-4 col-md-2" wire:click="acceptedBySeller({{ $order_from_other_seller }})" wire:target="acceptedBySeller({{ $order_from_other_seller }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Accept the order">
                                                            <span wire:target="acceptedBySeller({{ $order_from_other_seller }})" wire:loading.remove>
                                                                Accept
                                                            </span>
                                                            <span wire:target="acceptedBySeller({{ $order_from_other_seller }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>

                                                        <button class="btn btn-danger col-3 col-md-2" wire:click="rejectedBySeller({{ $order_from_other_seller->id }}, {{ $order_from_other_seller->customer_lat }}, {{ $order_from_other_seller->customer_lon }})" wire:target="rejectedBySeller({{ $order_from_other_seller->id }}, {{ $order_from_other_seller->customer_lat }}, {{ $order_from_other_seller->customer_lon }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled" title="Reject the order">
                                                            <span wire:target="rejectedBySeller({{ $order_from_other_seller->id }}, {{ $order_from_other_seller->customer_lat }}, {{ $order_from_other_seller->customer_lon }})" wire:loading.remove>
                                                                Reject
                                                            </span>
                                                            <span wire:target="rejectedBySeller({{ $order_from_other_seller->id }}, {{ $order_from_other_seller->customer_lat }}, {{ $order_from_other_seller->customer_lon }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($order_from_other_seller->order_status === 'accepted')
                                                        <button class="btn btn-warning col-4 col-md-2" wire:click="readyBySeller({{ $order_from_other_seller }})" wire:target="readyBySeller({{ $order_from_other_seller }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Mark the order as ready">
                                                            <span wire:target="readyBySeller({{ $order_from_other_seller }})" wire:loading.remove>
                                                                Ready
                                                            </span>
                                                            <span wire:target="readyBySeller({{ $order_from_other_seller }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($order_from_other_seller->order_status === 'ready')
                                                        <button class="btn btn-warning col-4 col-md-2" wire:click="deliveredBySeller({{ $order_from_other_seller }})" wire:target="deliveredBySeller({{ $order_from_other_seller }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Mark the order as delivered">
                                                            <span wire:target="deliveredBySeller({{ $order_from_other_seller }})" wire:loading.remove>
                                                                Deliver
                                                            </span>
                                                            <span wire:target="deliveredBySeller({{ $order_from_other_seller }})" wire:loading>
                                                                <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </button>
                                                    @endif

                                                    @if ($order_from_other_seller->order_status === 'delivered')
                                                        <button class="btn btn-dark col-4 col-md-2" title="You have delivered the order successfully">
                                                            Delivered 🥳
                                                        </button>
                                                    @endif

                                                    @if ($order_from_other_seller->order_status === 'onTheWay')
                                                        <button class="btn btn-dark col-4 col-md-2" title="Our delivery boy is delivering your order">
                                                            On The Way 😊
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="col-12 col-md-2 mt-md-1 mt-4">
                                                    @if ($order_from_other_seller->order_status === 'pending')
                                                        @if ($this->isTheOrderOlderThen($order_holding_minutes, $order_from_other_seller->created_at))
                                                            <p class="fs-3 fw-bold text-danger">
                                                                Time Over...
                                                            </p>
                                                        @else
                                                            <p class="fs-3 fw-bold timer" id={{ $order_from_other_seller->id }}>
                                                                {{-- Timer will render here --}}
                                                            </p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Order#</b></td>
                                        <td>{{ $order_from_other_seller->id }}</td>
                                        <td><b>Order Status</b></td>
                                        <td><span class="badge badge-warning">{{ $order_from_other_seller->order_status }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Placed At</b></td>
                                        <td>{{ $order_from_other_seller->created_at }}</td>
                                        <td><b>Order Type</b></td>
                                        <td><span class="badge badge-info">{{ $order_from_other_seller->type }}</span></td>
                                    </tr>

                                    <tr>
                                        <td><b>Order Total</b></td>
                                        <td>£{{ $order_from_other_seller->order_total }}</td>
                                        <td><b>Payment Status</b></td>
                                        <td><span class="badge badge-primary">{{ $order_from_other_seller->payment_status }}</span></td>
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
                                        @if (str_contains($order_from_other_seller->product->feature_img, 'https://'))
                                            <img class="d-block m-auto" src="{{ $order_from_other_seller->product->feature_img }}">
                                        @else
                                            <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $order_from_other_seller->product->feature_img }}">
                                        @endif
                                    </span>
                                </div>
                                <div class="col-12 col-sm-10">
                                    <table class="table">
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Id: Remove in production</b></td>
                                            <td class="col-8">{{ $order_from_other_seller->product_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Name:</b></td>
                                            <td class="col-8">{{ $order_from_other_seller->product->product_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Category:</b></td>
                                            <td class="col-8">{{ $order_from_other_seller->product->category->category_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>SKU:</b></td>
                                            <td class="col-8">{{ $order_from_other_seller->product->sku }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>QTY:</b></td>
                                            <td class="col-8">{{ $order_from_other_seller->product_qty }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Price:</b></td>
                                            <td class="col-8">£{{ $order_from_other_seller->product_price }}</td>
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
            <h2>No orders from other stores yet... :(</h2>
        @endforelse

        {{-- @if (!empty($data))
            <div class="row">
                <div class="col-md-12">
                    {{ $data->links() }}
                </div>
            </div>
        @endif --}}

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

        //Params: minutes, seconds, holdingMinutes, holdingSeconds
        const timerManager = new TimerManager({{ $order_holding_minutes }}, 00, 5, 59);
        timerManager.start();
    </script>

</div>
