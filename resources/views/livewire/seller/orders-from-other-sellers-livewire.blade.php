<div class="container-xxl flex-grow-1 container-p-y">

    @php
        use App\Services\ProductServices;
        use App\Models\ProductsByBuyer;
        use App\Models\Products;
    @endphp

    <x-session-messages />

    {{-- ************************************ No Other Sellers Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="noOtherSellersModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="close" wire:click="resetComponent" aria-label="Close"
                        data-bs-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form wire:submit="cancelOrder({{ $orderId }})" method="post">
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
                        <button type="button" class="btn btn-secondary" wire:click="resetComponent"
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
    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1 text-site-primary">Orders From Other Sellers</h4>
        </div>
        @forelse ($data as $order)
            <!-- Single Order Content -->
            {{-- <div class="col-12 p-2"
                wire:poll.60000ms="moveToAnotherSeller(
                                    {{ $order->id }}, 
                                    '{{ $order->order_status }}', 
                                    {{ $order->customer_lat }}, 
                                    {{ $order->customer_lon }}, 
                                    '{{ $order->moved_at }}'
                                )"> --}}
            <div class="col-12 p-2">
                <div class="card">
                    <div class="card-body py-1 px-2">
                        <!-- Order Header -->
                        <div class="p-2 mb-2">

                            <livewire:common.orders-header-livewire :$order :key="'orders-header-livewire-' . $order->id" />

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
                                            <img class="d-block m-auto"
                                                src="{{ config('constants.BUCKET') . $order->product->feature_img }}">
                                        @endif
                                    </span>
                                </div>
                                <div class="col-12 col-sm-10">
                                    <table class="table">
                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Product Name</b></td>
                                            <td class="col-8">{{ $order->product->product_name }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Qty</b></td>
                                            <td class="col-8">{{ $order->product_qty }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Category</b></td>
                                            <td class="col-8">{{ $order->product->category?->category_name }}</td>
                                        </tr>

                                        @if ($order->product_belongs_to_type === (new ProductsByBuyer())->getMorphClass())
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Budget</b></td>
                                                <td class="col-8">£{{ $order->product_price }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Price</b></td>
                                                <td class="col-8">£{{ $order->product_price }}</td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Weight</b></td>
                                            <td class="col-8">{{ $order->product->weight }}kg</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Brand</b></td>
                                            <td class="col-8">{{ $order->product->brand }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Part Number</b></td>
                                            <td class="col-8">{{ $order->product->part_number }}</td>
                                        </tr>

                                        @if ($order->product_belongs_to_type === (new ProductsByBuyer())->getMorphClass())
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Colors</b></td>
                                                <td class="col-8">
                                                    {{ ProductServices::jsonDecodeColors($order->product?->colors) }}
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Transport Vehicle</b></td>
                                            <td class="col-8">{{ $order->product->transport_vehicle }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Height</b></td>
                                            <td class="col-8">{{ $order->product->height }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Width</b></td>
                                            <td class="col-8">{{ $order->product->width }}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-4 text-site-primary"><b>Length</b></td>
                                            <td class="col-8">{{ $order->product->length }}</td>
                                        </tr>

                                        @if ($order->product_belongs_to_type === (new Products())->getMorphClass())
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>SKU</b></td>
                                                <td class="col-8">{{ $order->product->sku }}</td>
                                            </tr>
                                        @endif

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
            <p class="text-dark text-center p-2 fs-3">No Orders From Other Sellers Yet 🥺</p>
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

        /* Params: minutes, seconds, holdingMinutes, holdingSeconds */
        const timerManager = new TimerManager({{ $orderHoldingMinutes }}, 00, 5, 59);
        timerManager.start();
    </script>

</div>
