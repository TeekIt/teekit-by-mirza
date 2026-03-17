{{-- <div class="container-xxl flex-grow-1 container-p-y">
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
                                <input type="text" class="form-control" placeholder="Enter van location"
                                    wire:model.live="vanLocation">
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group">
                                <select class="form-control" wire:model.live="nearBySellerId">
                                    <option value="">Select a near by seller</option>
                                    @foreach ($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Product Name (optional) --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Enter product name (optional)"
                                    wire:model.live="productSearch">
                            </div>
                        </div>
                    </div>

                    {{-- Search Button --}}
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn site-primary-yellow-bg w-100 rounded-pill py-2"
                                wire:click="search" wire:target="search" wire:loading.class="btn-dark"
                                wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled"
                                @disabled(!trim($vanLocation) || !$nearBySellerId)>
                                <span wire:target="search" wire:loading.remove>
                                    Search
                                </span>
                                <span wire:target="search" wire:loading>
                                    <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($showInventoryGrid && $products)
                <section class="section-products mt-4">
                    <div class="container px-0">
                        <div class="row">
                            @forelse ($products as $inventory)
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2">
                                    <div
                                        class="single-product bg-white p-2 rounded @if ($inventory->status->value == ProductStatusEnum::DISABLE->value) disabled-product @endif">
                                        @php
                                            if (str_contains($inventory->feature_img, 'https://')) {
                                                $featureImageUrl = $inventory->feature_img;
                                            } else {
                                                $featureImageUrl = config('constants.BUCKET') . $inventory->feature_img;
                                            }
                                        @endphp
                                        <div class="part-1"
                                            style="background:url('{{ $featureImageUrl }}') no-repeat center;">
                                            <ul>
                                                <li>
                                                    <a wire:click="markAsFeatured('{{ $inventory->id }}', 1)"
                                                        wire:target="markAsFeatured('{{ $inventory->id }}', 1)"
                                                        wire:loading.attr="disabled" title="Add to Cart">
                                                        <span class="fas fa-cart-arrow-down"
                                                            wire:target="markAsFeatured('{{ $inventory->id }}', 1)"
                                                            wire:loading.remove></span>
                                                        <span wire:target="markAsFeatured('{{ $inventory->id }}', 1)"
                                                            wire:loading>
                                                            <span class="spinner-border spinner-border-sm"
                                                                role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="part-2 px-2">
                                            <h3 class="product-title" title="{{ $inventory->product_name }}">
                                                {{ Str::limit($inventory->product_name, 25) }}
                                            </h3>
                                            <h5 class="rating">
                                                {{ $inventory->category->category_name ?? 'Uncategorized' }}</h5>
                                            <h4>SKU: {{ $inventory->sku }}</h4>
                                            <h5>£{{ $inventory->price }}</h5>
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
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

</div> --}}
