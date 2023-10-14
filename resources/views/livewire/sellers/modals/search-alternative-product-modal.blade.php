<div>
    <div class="alert alert-warning" role="alert">
        <h5>Please Read Carefully!</h5>
        <p>
            <b>Must call the customer before searching an alternative</b> to know your customer's choice. If you don't call & the customer complains about the alternative product which you have selected by yourself then Teekit may cancel your whole order with full refund to the customer.
        </p>
        <h4>Customer Name: {{ $name }}</h4>
        <h4>Cutomer Contact: {{ $phone_number }}</h4>
    </div>
    @if (empty($product))
        {{-- Search Container --}}
        <div class="row">
            <div class="col-12 form-floating">
                <input type="search" wire:model.debounce.500ms="search" class="form-control" placeholder="Search alternative product">
                <label>Search alternative product</label>
            </div>
            {{-- <div class="col-12 col-sm-3 btn-group border border-danger" role="group">
            <button type="button" class="btn btn-site-primary py-3 w-100 px-0" title="Add this product to the order">
                <span class="fas fa-plus"></span>
            </button>
            <button type="button" class="btn btn-danger py-3 w-100 px-0" title="Remove the inserted product">
                <span class="fas fa-minus"></span>
            </button>
        </div> --}}
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
            <div class="text-center" wire:loading wire:target="search">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
    @else
        {{-- Product Container --}}
        <div class="row mt-3">
            <div class="col-md-2">
                <span class="img-container">
                    @if (str_contains($product->feature_img, 'https://'))
                        <img class="d-block m-auto" src="{{ asset($product->feature_img) }}">
                    @else
                        <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $product->feature_img }}">
                    @endif
                </span>
            </div>
            <div class="col-10">
                <table class="table">
                    <tr>
                        <td class="text-site-primary"><b>Product Name:</b></td>
                        <td>{{ $product->product_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-site-primary"><b>Category:</b></td>
                        <td>{{ $product->category->category_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-site-primary"><b>SKU:</b></td>
                        <td>{{ $product->sku }}</td>
                    </tr>
                    <tr>
                        <td class="text-site-primary"><b>Available QTY:</b></td>
                        <td>{{ $product->quantity->qty }}</td>
                    </tr>
                    <tr>
                        <td class="text-site-primary"><b>Price:</b></td>
                        <td>£{{ $product->price }}</td>
                    </tr>
                    <tr>
                        <td class="text-site-primary"><b>QTY you want to add:</b></td>
                        <td>
                            <input type="number" class="form-control col-3">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" class="btn btn-outline-danger py-3 px-0 w-100" wire:click="removeProduct" wire:target="removeProduct" wire:loading.class="btn-outline-dark" wire:loading.class.remove="btn-outline-danger" wire:loading.attr="disabled" title="Remove this product to select another one">
                                <span class="fas fa-trash" wire:target="removeProduct" wire:loading.remove></span>
                                <span wire:target="removeProduct" wire:loading>
                                    <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                </span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endif
</div>
