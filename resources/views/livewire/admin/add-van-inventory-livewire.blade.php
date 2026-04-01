<div class="container-xxl flex-grow-1 container-p-y">
    @php
        use App\Enums\ProductStatusEnum;
        use Illuminate\Support\Str;
    @endphp

    <x-session-messages />

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12">
                    <h4 class="py-4 my-1 text-site-primary">Add Van Inventory</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    {{-- Van Location + Nearby Seller --}}
                    <div class="row mb-3">
                        <div class="col-7">
                            <div class="form-group">
                                {{-- The "id" attribute is set to "pickupAddress" so we can align it with the
                                    CustomGoogleMapsClass in the scripts.blade.php file --}}
                                <input type="text" class="form-control" placeholder="Enter van location"
                                    id="pickupAddress" required>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group">
                                <select class="form-control" wire:model.live="nearBySellerId">
                                    <option value="">Select a near by seller</option>
                                    @foreach ($nearbySellers as $singleIndex)
                                        <option value="{{ $singleIndex['id'] }}">
                                            {{ $singleIndex['business_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <form wire:submit="performSearch">
                        {{-- Product Name (optional) --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <input type="text" class="form-control"
                                        placeholder="Enter product name (optional)" wire:model.live="search">
                                </div>
                            </div>
                        </div>

                        {{-- Search Button --}}
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn site-primary-yellow-bg w-100 rounded-pill py-2"
                                    wire:target="performSearch" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled"
                                    @disabled(!trim($vanLocation) || !$nearBySellerId)>
                                    <span wire:target="performSearch" wire:loading.remove>
                                        Search
                                    </span>
                                    <span wire:target="performSearch" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($showInventoryGrid && $inventory)
                <section class="section-products mt-4">
                    <div class="container px-0">
                        <div class="row">
                            @forelse ($inventory as $singleIndex)
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2">
                                    <div
                                        class="single-product bg-white p-2 rounded @if ($singleIndex->status->value == ProductStatusEnum::DISABLE->value) disabled-product @endif">
                                        @php
                                            if (str_contains($singleIndex->feature_img, 'https://')) {
                                                $featureImageUrl = $singleIndex->feature_img;
                                            } else {
                                                $featureImageUrl =
                                                    config('constants.BUCKET') . $singleIndex->feature_img;
                                            }
                                        @endphp
                                        <div class="part-1"
                                            style="background:url('{{ $featureImageUrl }}') no-repeat center;">
                                            <ul>
                                                <li>
                                                    <a wire:click="addToCart({{ $singleIndex->id }})"
                                                        wire:target="addToCart({{ $singleIndex->id }})"
                                                        wire:loading.attr="disabled" title="Add to Cart">
                                                        <span class="fas fa-cart-arrow-down"
                                                            wire:target="addToCart({{ $singleIndex->id }})"
                                                            wire:loading.remove></span>
                                                        <span wire:target="addToCart({{ $singleIndex->id }})"
                                                            wire:loading>
                                                            <span class="spinner-border spinner-border-sm"
                                                                role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="part-2 px-2">
                                            <h3 class="product-title" title="{{ $singleIndex->product_name }}">
                                                {{ Str::limit($singleIndex->product_name, 25) }}
                                            </h3>
                                            <h5 class="rating">{{ $singleIndex->category->category_name }}</h5>
                                            <h4>SKU: {{ $singleIndex->sku }}</h4>
                                            <h5>£{{ $singleIndex->price }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-dark text-center p-2 fs-3">No Products Found</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                {{ $inventory->links() }}
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    {{-- Cart Drawer --}}
    <div>
        <button type="button"
            class="btn p-0 border-0 bg-transparent shadow-none position-fixed bottom-0 end-0 me-4 mb-4 z-3 d-inline-flex align-items-center justify-content-center custom-cart-icon position-relative"
            data-bs-toggle="offcanvas" data-bs-target="#vansCartDrawer" aria-controls="vansCartDrawer"
            aria-label="Open cart drawer">
            <i class="fas fa-cart-arrow-down text-site-primary fa-3x "></i>
            <span
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill site-primary-yellow-bg text-dark">
                {{ $cartItemsCount }}
            </span>
        </button>

        <div wire:ignore.self class="offcanvas offcanvas-end bg-white" tabindex="-1" id="vansCartDrawer"
            aria-labelledby="vansCartDrawerLabel" data-bs-backdrop="false" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="vansCartDrawerLabel">Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body d-flex flex-column">
                <div class="flex-grow-1 overflow-auto pe-1">
                    @forelse ($cartItems as $cartItem)
                        <div class="card border rounded-3 mb-2 p-2">
                            <div class="card-body p-2 position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 custom-btn-close-sm"
                                    wire:click="removeCartItem({{ $cartItem['id'] }})"
                                    wire:target="removeCartItem({{ $cartItem['id'] }})"
                                    wire:loading.attr="disabled"
                                    aria-label="Remove item"></button>
                                <div class="d-flex align-items-start gap-2">
                                    <img src="{{ $cartItem['image'] }}" alt="Product Image" class="img-fluid"
                                        style="width: 64px; height: 64px; object-fit: cover;">
                                    <div class="w-100">
                                        <h6 class="mt-1 mb-3">{{ $cartItem['title'] }}</h6>
                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <div class="input-group input-group-sm" style="width: 130px;">
                                                <button type="button" class="btn btn-outline-secondary px-2 rounded-pill rounded-end-0"
                                                    wire:click="decreaseCartItemQty({{ $cartItem['id'] }})"
                                                    wire:target="decreaseCartItemQty({{ $cartItem['id'] }})"
                                                    wire:loading.attr="disabled">-</button>
                                                <input type="number" min="1"
                                                    class="form-control text-center"
                                                    value="{{ $cartItem['qty'] }}"
                                                    wire:change="updateCartItemQty({{ $cartItem['id'] }}, $event.target.value)">
                                                <button type="button" class="btn btn-outline-secondary px-2 rounded-pill rounded-start-0"
                                                    wire:click="increaseCartItemQty({{ $cartItem['id'] }})"
                                                    wire:target="increaseCartItemQty({{ $cartItem['id'] }})"
                                                    wire:loading.attr="disabled">+</button>
                                            </div>
                                            <span class="mr-2">Price: £{{ number_format($cartItem['price'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">Basket is empty</p>
                    @endforelse
                </div>

                <div class="d-flex justify-content-between align-items-center border-top py-3 fs-5">
                    <strong>Total</strong>
                    <strong>£{{ number_format($cartTotal, 2) }}</strong>
                </div>

                <div class="d-grid gap-2 mt-auto">
                    <button type="button" class="btn site-primary-bg text-white w-100 rounded-pill">
                        Checkout
                    </button>
                    <button type="button" class="btn btn-secondary w-100 rounded-pill" data-bs-dismiss="offcanvas">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
