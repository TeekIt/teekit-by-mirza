<div>
    <div class="alert alert-warning" role="alert">
        <h5>Please Read Carefully!</h5>
        <p>
            <b>Must call the customer before searching an alternative</b> to know your customer's choice. If you don't call & the customer complains about the alternative product which you have selected by yourself then Teekit may cancel your whole order with full refund to the customer.
        </p>
        <h4>Customer Name: {{ $receiver_name }}</h4>
        <h4>Customer Contact: {{ $phone_number }}</h4>
    </div>
    @if (empty($product_details))
        {{-- Search Container --}}
        <div class="row">
            <div class="form-group">
                <div class="input-group">
                    <input type="search" wire:model.debounce.500ms="search" class="form-control" placeholder="Search alternative product">
                    <div class="input-group-append">
                        <button class="btn btn-site-primary"><i class='fas fa-search'></i></button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Products List --}}
        <div class="mt-3">
            <table class="table" wire:loading.remove wire:target="search">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $single_product)
                        <tr>
                            <td>{{ $single_product->product_name }}</td>
                            <td>{{ $single_product->qty }}</td>
                            <td>£{{ $single_product->price }}</td>
                            <td>
                                <button type="button" class="btn btn-site-primary" wire:click="addProduct({{ $single_product->prod_id }})" wire:target="addProduct" wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled" title="Add this product to the order">
                                    <span class="fas fa-plus" wire:target="addProduct({{ $single_product->prod_id }})" wire:loading.remove></span>
                                    <span wire:target="addProduct({{ $single_product->prod_id }})" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            {{ $products->links() }}
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="col-12 text-center" wire:loading wire:target="search">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
    @else
        {{-- Product Container --}}
        <div class="row mt-3">
            <div class="col-md-12 col-lg-2">
                <span class="img-container">
                    @if (str_contains($product_details->feature_img, 'https://'))
                        <img class="d-block m-auto" src="{{ asset($product_details->feature_img) }}">
                    @else
                        <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $product_details->feature_img }}">
                    @endif
                </span>
            </div>
            <div class="col-md-12 col-lg-10">
                <form wire:submit.prevent="addProductIntoOrder({{ $product_details }})" method="POST">
                    <table class="table">
                        <tr>
                            <td class="text-site-primary"><b>Product Name:</b></td>
                            <td>{{ $product_details->product_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-site-primary"><b>Category:</b></td>
                            <td>{{ $product_details->category->category_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-site-primary"><b>SKU:</b></td>
                            <td>{{ $product_details->sku }}</td>
                        </tr>
                        <tr>
                            <td class="text-site-primary"><b>Available QTY:</b></td>
                            <td>{{ $product_details->quantity->qty }}</td>
                        </tr>
                        <tr>
                            <td class="text-site-primary"><b>Price:</b></td>
                            <td>£{{ $product_details->price }}</td>
                        </tr>
                        <tr>
                            <td class="text-site-primary"><b>QTY you want to add:</b></td>
                            <td>
                                <input type="number" wire:model.defer="selected_qty" class="col-3 form-control">
                                @if (session()->has('qty_should_not_be_greater'))
                                    <p class="text-danger">{{ session()->get('qty_should_not_be_greater') }}</p>
                                @endif
                                <small class="text-danger">
                                    @error('selected_qty')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="col-12 btn-group" role="group">
                                    <button type="submit" class="btn btn-outline-success py-3 px-0 w-100" wire:target="addProductIntoOrder" wire:loading.class="btn-outline-dark" wire:loading.class.remove="btn-outline-success" wire:loading.attr="disabled" title="Add this product to the order">
                                        <span class="fas fa-plus" wire:target="addProductIntoOrder" wire:loading.remove></span>
                                        <span wire:target="addProductIntoOrder" wire:loading>
                                            <span class="spinner-border spinner-border-sm text-dark" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger py-3 px-0 w-100" wire:click="removeAlternativeProduct" wire:target="removeAlternativeProduct" wire:loading.class="btn-outline-dark" wire:loading.class.remove="btn-outline-danger" wire:loading.attr="disabled" title="Remove this product to select another one">
                                        <span class="fas fa-trash" wire:target="removeAlternativeProduct" wire:loading.remove></span>
                                        <span wire:target="removeAlternativeProduct" wire:loading>
                                            <span class="spinner-border spinner-border-sm text-dark" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    @endif
</div>
