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
    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1">Orders From Other Sellers 2</h4>
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
                                    <tr class="col-12">
                                        <td colspan="6 col-12">
                                            <div class="row">
                                                <div class="col-12 col-md-10">
                                                    <button class="btn btn-warning col-3 col-md-2" title="Hold the order">
                                                        <span wire:target="" wire:loading.remove>
                                                            Hold
                                                        </span>
                                                        {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                    </button>

                                                    <button class="btn btn-success col-4 col-md-2" title="Accept the order">
                                                        <span wire:target="" wire:loading.remove>
                                                            Accept
                                                        </span>
                                                        {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                    </button>

                                                    <button class="btn btn-danger col-3 col-md-2" title="Reject the order">
                                                        <span wire:target="" wire:loading.remove>
                                                            Reject
                                                        </span>
                                                        {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                    </button>
                                                </div>
                                                <div class="col-12 col-md-2 mt-md-1 mt-4">
                                                    {{-- @dd($order->created_at->diffInMinutes(\Carbon\Carbon::now())) --}}
                                                    @if ($order->created_at->diffInDays(\Carbon\Carbon::now()) > 2)
                                                        <p class="fs-3 fw-bold text-danger">
                                                            Time Over...
                                                        </p>
                                                    @else
                                                        <p class="fs-3 fw-bold timer" id={{ $order->id }}>
                                                            {{-- 02:00 --}}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
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
                            <!-- Order Items -->
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <span class="img-container">
                                        @if (str_contains($order->product->feature_img, 'https://'))
                                            <img class="d-block m-auto" src="{{ $order->product->feature_img }}">
                                        @else
                                            <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $order->product->feature_img }}">
                                        @endif
                                    </span>
                                </div>
                                <div class="col-12 col-sm-10">
                                    <table class="table">
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Id: Remove in production</b></td>
                                            <td class="col-8">{{ $order->product_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Name:</b></td>
                                            <td class="col-8">{{ $order->product->product_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Category:</b></td>
                                            <td class="col-8">{{ $order->product->category->category_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>SKU:</b></td>
                                            <td class="col-8">{{ $order->product->sku }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>QTY:</b></td>
                                            <td class="col-8">{{ $order->product_qty }}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Price:</b></td>
                                            <td class="col-8">£{{ $order->product_price }}</td>
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
        var initialMinutes = 2;
        var initialSeconds = 10;
        const updateTimers = () => {
            let timerElements = document.querySelectorAll('.timer');

            timerElements.forEach(function(timerElement) {

                let timerEelementId = timerElement.getAttribute('id');
                let localStorageValue = localStorage.getItem(timerEelementId);

                if (localStorageValue !== null) {
                    var minutes = localStorageValue.split(':')[0];
                    var seconds = localStorageValue.split(':')[1];
                } else {
                    var minutes = initialMinutes;
                    var seconds = initialSeconds;
                }
                // Decrement seconds
                seconds--;
                // Adjust minutes if seconds reach 0
                if (seconds < 0) {
                    seconds = 59;
                    minutes--;
                    minutes = (minutes === -1) ? 0 : minutes;
                }
                // Update timer display
                if (seconds > 0 && minutes > 0) {
                    timerElement.textContent = padZero(minutes) + ':' + padZero(seconds);
                    setLocalStorage(timerEelementId, minutes + ':' + seconds);
                } else {
                    timerElement.textContent = '00:00';
                    setLocalStorage(timerEelementId, '00:00');
                }
            });
        }

        // Function to pad single digit numbers with leading zeros
        const padZero = (num) => (num < 10 ? '0' : '') + num;

        const setLocalStorage = (key, value) => localStorage.setItem(key, value);

        // Update timers every second
        setInterval(updateTimers, 1000);

        // Load timer state from local storage for each timer
        document.addEventListener('DOMContentLoaded', function() {
            let timerElements = document.querySelectorAll('.timer');

            timerElements.forEach(function(timerElement) {
                let timerKey = timerElement.getAttribute('id');
                let timerValue = localStorage.getItem(timerKey);
                if (timerValue !== null) {
                    timerElement.textContent = timerValue;
                }
            });
        });
    </script>
</div>
