<div class="container-xxl flex-grow-1 container-p-y">
    @php
        use App\Enums\ProductStatusEnum;
        use App\Enums\OrderByEnum;
        use Illuminate\Support\Str;
    @endphp

    <x-session-messages />

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12 col-sm-6 col-md-7 col-xl-9">
                    <h4 class="py-4 my-1 text-site-primary">
                        Add Sellers For Company
                    </h4>
                </div>

                <div class="col-12 col-md-5 col-xl-3 d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-site-primary my-3 py-3" style="width: calc((100% - 16px) / 3);"
                        wire:click="addSellerAndProducts" wire:target="addSellerAndProducts"
                        wire:loading.attr="disabled" @disabled(!$selectedProducts) title="Add Seller & Products">
                        <span wire:target="addSellerAndProducts" wire:loading.remove>
                            <i class="fas fa-plus"></i>
                        </span>
                        <span wire:target="addSellerAndProducts" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center mb-4">
                <div class="col-12">
                    {{-- Seller Dropdown --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <select class="form-control py-2" wire:model.live="sellerId">
                                    <option value="">Select a seller</option>
                                    @foreach ($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($sellerId)
                        {{-- Search & Filters --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <input type="text" class="form-control"
                                        placeholder="Enter product name (optional)"
                                        wire:model.live.debounce.500ms="search">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 g-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control py-2" wire:model.live="orderBy">
                                        <option value="{{ OrderByEnum::DESC }}">Oldest First</option>
                                        <option value="{{ OrderByEnum::ASC }}">Newest First</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control py-2" wire:model.live="orderByPrice">
                                        <option value="">Filter by price</option>
                                        <option value="{{ OrderByEnum::DESC }}">High to Low</option>
                                        <option value="{{ OrderByEnum::ASC }}">Low to High</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control py-2" wire:model.live="categoryId">
                                        <option value="">Filter by category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($sellerId && $products)
                <section class="section-products mt-4">
                    <div class="container px-0">
                        <div class="row">
                            @forelse ($products as $product)
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2" wire:key="product-card-{{ $product->id }}">
                                    <div id="productItem"
                                        class="single-product bg-white p-2 rounded @if ($product->status->value == ProductStatusEnum::DISABLE->value) disabled-product @endif">
                                        @php
                                            if (str_contains($product->feature_img, 'https://')) {
                                                $featureImageUrl = $product->feature_img;
                                            } else {
                                                $featureImageUrl = config('constants.BUCKET') . $product->feature_img;
                                            }
                                        @endphp
                                        <div class="part-1"
                                            style="background:url('{{ $featureImageUrl }}') no-repeat center; position: relative;">

                                            {{-- Checkbox at top left corner --}}
                                            <div class="position-absolute" style="left: 30px; z-index: 10;">
                                                <input type="checkbox"
                                                    class="form-check-input select-checkbox cursor-pointer"
                                                    style="width: 24px; height: 24px; border: 2px solid #bbb;"
                                                    value="{{ $product->id }}" wire:model.live="selectedProducts"
                                                    wire:key="product-chk-{{ $product->id }}" {{-- id="product_chk_{{ $product->id }}" --}}>
                                            </div>
                                        </div>
                                        <div class="part-2 px-2">
                                            <h3 class="product-title" title="{{ $product->product_name }}">
                                                {{ Str::limit($product->product_name, 25) }}
                                            </h3>
                                            <h5 class="rating">{{ $product->category->category_name }}</h5>
                                            <h4>SKU: {{ $product->sku }}</h4>
                                            <h5>£{{ $product->price }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-dark text-center p-2 fs-3">No Products Found</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <div class="py-5">
                    <p class="text-dark fs-2 p-2 text-center">Please select a seller</p>
                </div>
            @endif
        </div>
    </div>
</div>
