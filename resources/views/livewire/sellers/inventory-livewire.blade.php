<div class="container-xxl flex-grow-1 container-p-y">
    @php
        use Illuminate\Support\Str;
    @endphp
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

    <div class="row">
        <div class="col-12 col-sm-12 col-md-4 py-4 my-2">
            <input type="text" wire:model.debounce.500ms="search" class="form-control" placeholder="Search here...">
        </div>
        <div class="col-12 col-sm-12 col-md-3 py-4 my-2">
            <select class="form-control" wire:model.debounce.500ms="category_id">
                <option value="0">Select category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-12 col-md-5 d-flex">
            @if (Auth::user()->role->name == 'child_seller')
                <button type="button" class="btn btn-primary col-12 col-sm-12 col-md-3 my-4 p-1 w-100 mx-1 rounded" title="Update bulk">
                    <i class="fas fa-angle-double-up"></i>
                </button>
            @else
                <button type="button" class="btn btn-warning my-4 p-1 w-100 mx-1 rounded" wire:click="toggleAllProducts(1)" wire:target="toggleAllProducts(1)" wire:loading.class="btn-dark" wire:loading.class.remove="btn-warning" wire:loading.attr="disabled" title="Enable All">
                    <span class="fa fa-toggle-on" wire:target="toggleAllProducts(1)" wire:loading.remove></span>
                    <span wire:target="toggleAllProducts(1)" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-danger text-white py-3 my-4 p-1 w-100 mx-1" wire:click="toggleAllProducts(0)" wire:target="toggleAllProducts(0)" wire:loading.class="btn-dark" wire:loading.class.remove="btn-danger" wire:loading.attr="disabled" title="Disable All">
                    <i class="fas fa-ban" wire:target="toggleAllProducts(0)" wire:loading.remove></i>
                    <span wire:target="toggleAllProducts(0)" wire:loading>
                        <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                    </span>
                </button>
                <a type="button" href="{{ route('seller.add.single.inventory.form') }}" class="btn btn-primary my-4 py-3 w-100 mx-1 px-0 " title="Add New">
                    <span class="fas fa-plus"></span>
                </a>
                {{-- <a type="button" href="{{ route('seller.add.bulk.inventory') }}" class="btn btn-primary my-4 py-3 w-100 mx-1 px-0" title="Add Bulk">
                    <span class="fas fa-plus"></span>
                    <span class="fas fa-boxes"></span>
                </a> --}}
            @endif
        </div>
    </div>

    @if (Auth::user()->role->name == 'seller')
        <div class="container-xxl flex-grow-1 container-p-y">
            <section class="section-products">
                <div class="container">
                    {{-- Featured Products - Begins --}}
                    @if (count($featured_products) > 0)
                        <div class="row">
                            <div class="col-lg-12 col-sm-12 col-md-12">
                                <h4 class="py-4 my-1">Featured</h4>
                            </div>
                            @foreach ($featured_products as $inventory)
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2">
                                    <div id="productItem" class="single-product bg-white p-2 rounded @if ($inventory->status == 0) disabled-product @endif">
                                        <div class="part-1" style="background:url('{{ asset($inventory->feature_img) }}') no-repeat center; ">
                                            <ul>
                                                @if ($inventory->status == 0)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 1)" wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading.attr="disabled" title="Enable Product">
                                                            <span class="fa fa-toggle-on" wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading.remove></span>
                                                            <span wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading>
                                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @elseif($inventory->status == 1)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 0)" wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading.attr="disabled" title="Disable Product">
                                                            <span class="fa fa-ban" wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading.remove></span>
                                                            <span wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading>
                                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a href="{{ route('seller.edit.inventory.form', ['product_id' => $inventory->id]) }}" title="Edit Product">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a wire:click="markAsFeatured('{{ $inventory->id }}', '0')" wire:target="markAsFeatured('{{ $inventory->id }}', '0')" wire:loading.attr="disabled" title="Undo Featured">
                                                        <span class="fa fa-undo" wire:target="markAsFeatured('{{ $inventory->id }}', '0')" wire:loading.remove></span>
                                                        <span wire:target="markAsFeatured('{{ $inventory->id }}', '0')" wire:loading>
                                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="part-2 px-2">
                                            <div class="col">
                                                <h2 class="product-title" title="{{ $inventory->product_name }}">
                                                    {{ Str::limit($inventory->product_name, 25) }}
                                                </h2>
                                                <h5>{{ $inventory->category->category_name }}</h5>
                                                <h4>SKU: {{ $inventory->sku }}</h4>
                                                <div>
                                                    <?php $rattings = app\Rattings::getRatting($inventory->id); ?>
                                                    @if (!empty($rattings['average']))
                                                        <?php $star = round($rattings['average']); ?>
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <span class="fa fa-star @if ($i <= $star) checked @endif">
                                                            </span>
                                                        @endfor
                                                    @endif
                                                </div>
                                                <h5>${{ $inventory->price }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    {{-- Featured Products - Ends --}}

                    <div class="row">
                        <div class="col-12">
                            <h4 class="py-4 my-1">Inventory</h4>
                        </div>
                        @forelse ($data as $key => $inventory)
                            <!-- Single Product -->
                            @if ($inventory->featured === 0)
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2">
                                    <div id="productItem" class="single-product bg-white p-2 rounded @if ($inventory->status == 0) disabled-product @endif">
                                        <div class="part-1" style="background:url('{{ asset($inventory->feature_img) }}') no-repeat center; ">
                                            {{-- <span class="discount">15% off</span>
                                               <span class="new">new</span> --}}
                                            <ul>
                                                @if ($inventory->status == 0)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 1)" wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading.attr="disabled" title="Enable Product">
                                                            <span class="fa fa-toggle-on" wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading.remove></span>
                                                            <span wire:target="toggleProduct('{{ $inventory->id }}', 1)" wire:loading>
                                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @elseif($inventory->status == 1)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 0)" wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading.attr="disabled" title="Disable Product">
                                                            <span class="fa fa-ban" wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading.remove></span>
                                                            <span wire:target="toggleProduct('{{ $inventory->id }}', 0)" wire:loading>
                                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a href="{{ route('seller.edit.inventory.form', ['product_id' => $inventory->id]) }}" title="Edit Product">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a wire:click="markAsFeatured('{{ $inventory->id }}', '1')" wire:target="markAsFeatured('{{ $inventory->id }}', '1')" wire:loading.attr="disabled" title="Mark as Featured">
                                                        <span class="fa fa-star" wire:target="markAsFeatured('{{ $inventory->id }}', '1')" wire:loading.remove></span>
                                                        <span wire:target="markAsFeatured('{{ $inventory->id }}', '1')" wire:loading>
                                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="part-2 px-2">
                                            <h3 class="product-title" title="{{ $inventory->product_name }}">
                                                {{ Str::limit($inventory->product_name, 25) }}
                                            </h3>
                                            <h5 class="rating">{{ $inventory->category->category_name }}</h5>
                                            <h4>SKU: {{ $inventory->sku }}</h4>
                                            <div class="col-md-6">
                                                {{-- <div class="ratting pl-3">
                                                    @if (!empty($inventory->rattings->avg('ratting')))
                                                        <?php $stars = round($inventory->rattings->avg('ratting')); ?>
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <span class="fa fa-star @if ($i <= $stars) checked @endif"></span>
                                                        @endfor
                                                    @endif
                                                </div> --}}
                                            </div>
                                            <h5>${{ $inventory->price }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <h4 class="text-dark text-center p-2">No Products Found :(</h4>
                        @endforelse
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{ $data->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @elseif (Auth::user()->role->name == 'child_seller')
        <div class="container">
            <div class="row">
                <table class="table">
                    <thead class="bg-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $key => $inventory)
                            <tr class="bg-white">
                                <td class="align-middle fit-content">
                                    @if (str_contains($inventory->feature_img, 'https://'))
                                        <img class="img-fluid rounded standard-img-size" src="{{ asset($inventory->feature_img) }}">
                                    @else
                                        <img class="img-fluid rounded-pill standard-img-size " src="{{ asset(config('constants.BUCKET') . $inventory->feature_img) }}">
                                    @endif
                                </td>
                                <td class="align-middle fit-content">
                                    <p class="fw-normal mb-1">{{ $inventory->product_name }}</p>
                                </td>
                                <td class="align-middle fit-content">
                                    <input type="number" class="form-control" style="width:80px;" min="0" wire:model.defer="quantity.{{ $key }}.qty">
                                </td>
                                <td class="align-middle fit-content">
                                    <button type="button" class="btn btn-success" wire:click="updateProductQuantity({{ $key }})" wire:target="updateProductQuantity({{ $key }})" wire:loading.class="btn-dark" wire:loading.class.remove="btn-success" wire:loading.attr="disabled" title="Update">
                                        <span class="fas fa-sync" wire:target="updateProductQuantity({{ $key }})" wire:loading.remove></span>
                                        <span wire:target="updateProductQuantity({{ $key }})" wire:loading>
                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <h4 class="text-dark text-center p-2">No Products Found :(</h4>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-12">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
