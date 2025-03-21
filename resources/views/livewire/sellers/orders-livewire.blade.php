<div class="container-xxl flex-grow-1 container-p-y">

    @php
        use App\Enums\UserChoicesEnum;
        use App\Enums\OrderStatusEnum;
        use App\Enums\OrderTypeEnum;
        use App\Models\ProductsByBuyer;
        use App\Products;
    @endphp

    <x-session-messages />

    {{-- ************************************ Search Alternative Product Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="searchAlternativeProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Alternative Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    @if (empty($orderId) || empty($currentProdId) || empty($currentProdQty) || empty($customerName) || empty($phoneNumber))
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    @else
                        <livewire:sellers.modals.search-alternative-product-modal :order_id="$orderId" :current_prod_id="$currentProdId"
                            :current_prod_qty="$currentProdQty" :customer_name="$customerName" :phone_number="$phoneNumber">
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetModal">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h2 class="text-danger">WARNING!</h2>
                        <p>Are you sure that you want to remove this product from the order??</p>
                        <p class="fw-bold">You can't undo this action</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-site-primary" wire:click="removeItemFromOrder"
                        wire:target="removeItemFromOrder" wire:loading.class="btn-dark"
                        wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="removeItemFromOrder" wire:loading.remove>
                            Confirm
                        </span>
                        <span wire:target="removeItemFromOrder" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status"
                                aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetModal">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
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
                                            <option value="{{ $singleIndex['business_name'] }}">
                                                {{ $singleIndex['business_name'] }}</option>
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
                    <button type="button" class="btn btn-site-primary" wire:click="sendItemToAnOtherStore"
                        wire:target="sendItemToAnOtherStore" wire:loading.class="btn-dark"
                        wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                        <span wire:target="sendItemToAnOtherStore" wire:loading.remove>
                            Send
                        </span>
                        <span wire:target="sendItemToAnOtherStore" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status"
                                aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetModal">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    @if (empty($customerName) || empty($phoneNumber))
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    @else
                        <div class="text-center">
                            <h2 class="text-danger"><i class="fas fa-phone-alt"></i> CALL THE CUSTOMER</h2>
                            <p class="fw-bold">Customer Name: {{ $customerName }}</p>
                            <p class="fw-bold">Customer Contact: {{ $phoneNumber }}</p>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="d-flex justify-content-center col-8 col-sm-6">
                                <select class="form-select">
                                    <option value="1">Search Alternative</option>
                                    <option value="2">Remove Product</option>
                                    <option value="3">Cancel</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-site-primary" wire:click="" wire:target=""
                        wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning"
                        wire:loading.attr="disabled">
                        <span wire:target="" wire:loading.remove="">
                            Select
                        </span>
                        <span wire:target="" wire:loading="">
                            <span class="spinner-border spinner-border-sm text-light" role="status"
                                aria-hidden="true"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetModal">
                        Cancel
                    </button>
                </div>
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
                <button type="submit" class="btn btn-site-primary my-4 p-1 w-100 mx-1" wire:target="search"
                    wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                    wire:loading.attr="disabled" title="Search">
                    <span class='fas fa-search' wire:target="search" wire:loading.remove></span>
                    <span wire:target="search" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status"
                            aria-hidden="true"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-primary my-4 p-1 w-100 mx-1" wire:click="resetThisPage"
                    wire:target="resetThisPage" wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary"
                    wire:loading.attr="disabled" title="Reset orders page">
                    <span class="fas fa-sync" wire:target="resetThisPage" wire:loading.remove></span>
                    <span wire:target="resetThisPage" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status"
                            aria-hidden="true"></span>
                    </span>
                </button>
            </div>
        </div>
    </form>
    <!-- /Content Header -->

    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1 text-site-primary">Orders</h4>
        </div>
        @forelse ($data as $order)
            <!-- Single Order Content -->
            <div class="col-12 p-2">
                <div class="card">
                    <div class="card-body py-1 px-2">
                        <!-- Order Header -->
                        <div class="p-2 mb-2">
                            <livewire:common.orders-header :order="$order"
                                wire:key="orders-header-{{ $order->id }}" />
                        </div>
                        <!-- /Order Header -->
                        <div class="card-text">
                            @foreach ($order->order_items as $orderItem)
                                <!-- Order Items Begins -->
                                <div class="row mb-2">
                                    <div class="col-md-2">
                                        <span class="img-container">
                                            @if (str_contains($orderItem->product->feature_img, 'https://'))
                                                <img class="d-block m-auto"
                                                    src="{{ asset($orderItem->product->feature_img) }}">
                                            @else
                                                <img class="d-block m-auto"
                                                    src="{{ config('constants.BUCKET') . $orderItem->product->feature_img }}">
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-12 col-sm-10">
                                        <table class="table">
                                            <tr>
                                                <td class="col-4 text-site-primary"><b>Product Name</b></td>
                                                <td class="col-8">{{ $orderItem->product->product_name }}</td>
                                            </tr>

                                            @if ($orderItem->product_belongs_to_type === (new Products())->getMorphClass())
                                                <tr>
                                                    <td class="col-4 text-site-primary"><b>Category</b></td>
                                                    <td class="col-8">
                                                        {{ $orderItem->product->category->category_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="col-4 text-site-primary"><b>SKU</b></td>
                                                    <td class="col-8">{{ $orderItem->product->sku }}</td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td class="col-4 text-site-primary"><b>QTY</b></td>
                                                <td class="col-8"> {{ $orderItem->product_qty }} </td>
                                            </tr>

                                            @if ($orderItem->product_belongs_to_type === (new Products())->getMorphClass())
                                                <tr>
                                                    <td class="col-4 text-site-primary"><b>Price</b></td>
                                                    <td class="col-8"> £{{ $orderItem->product->price }} </td>
                                                </tr>
                                                @if ($order->order_status == OrderStatusEnum::PENDING->value)
                                                    <tr>
                                                        <td class="col-4 text-site-primary">
                                                            <b>I don't have this product!</b>
                                                            <button type="button" class="btn btn-site-primary"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="If you don't have this product then go for the option selected by the buyer by clicking the front button">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </button>
                                                        </td>
                                                        <td class="col-8">
                                                            @if ($orderItem->user_choice === UserChoicesEnum::ALTERNATIVE_PRODUCT->value)
                                                                <button type="button" class="btn btn-site-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#searchAlternativeProductModal"
                                                                    wire:click="renderSAPModal({{ $order->id }}, {{ $orderItem->product->id }}, {{ $orderItem->product_qty }}, '{{ $order->customer_name }}', '{{ $order->phone_number }}')">
                                                                    <i class="fas fa-search"></i>
                                                                    Search Alternative
                                                                </button>
                                                            @elseif ($orderItem->user_choice === UserChoicesEnum::REMOVE_PRODUCT->value)
                                                                <button type="button" class="btn btn-site-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#removeItemFromOrderModel"
                                                                    wire:click="renderRemoveItemModal({{ $orderItem }})">
                                                                    <i class="fas fa-minus-circle"></i>
                                                                    Remove Product
                                                                </button>
                                                            @elseif ($orderItem->user_choice === UserChoicesEnum::SEND_TO_OTHER_STORES->value)
                                                                <button type="button" class="btn btn-site-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#sendToOtherStoresModal"
                                                                    wire:click="renderSTOSModal({{ $order->id }})">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                    Send To Other Stores
                                                                </button>
                                                            @elseif ($orderItem->user_choice === UserChoicesEnum::CALL_ME->value)
                                                                <button type="button" class="btn btn-site-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#showCustomerContactModel"
                                                                    wire:click="renderCustomerContactModal('{{ $order->customer_name }}', '{{ $order->phone_number }}')">
                                                                    <i class="fas fa-phone-alt"></i>
                                                                    Call The Customer
                                                                </button>
                                                            @elseif ($orderItem->user_choice === UserChoicesEnum::CANCEL_ORDER->value)
                                                                <button type="button" class="btn btn-site-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#searchAlternativeProductModal"
                                                                    wire:click="renderSAPModal({{ $order->id }}, {{ $orderItem->product->id }}, {{ $orderItem->product_qty }}, '{{ $order->customer_name }}', '{{ $order->phone_number }}')">
                                                                    <i class="fas fa-times"></i>
                                                                    Cancel Order
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif

                                            @if ($orderItem->product_belongs_to_type === (new ProductsByBuyer())->getMorphClass())
                                                <tr>
                                                    <td class="col-4 text-site-primary"><b>Price</b></td>
                                                    <td class="col-8"> £{{ $orderItem->product->max_price }} </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                <!-- Order Items Ends -->
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Single Order Content -->
        @empty
            <p class="text-dark text-center p-2 fs-3">No Orders Yet 🥺</p>
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
