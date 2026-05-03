<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Edit Inventory Modal ************************************ --}}
    {{-- <div wire:ignore.self class="modal fade" id="editVanInventoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form wire:submit="updateVanInventory">
                    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Van Inventory</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                wire:click="resetComponent"></button>
                        </div>
                        <div class="col-12 my-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sellerId">Seller ID</label>
                                        <input type="number" class="form-control" id="sellerId"
                                            placeholder="Enter seller id..." wire:model="sellerId">
                                    </div>
                                    <small class="text-danger">
                                        @error('sellerId')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="categoryId">Category</label>
                                        <select class="form-control" id="categoryId" wire:model="categoryId">
                                            <option value="">Select category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('categoryId')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="productVanId">Van ID</label>
                                        <input type="number" class="form-control" id="productVanId"
                                            placeholder="Enter van id..." wire:model="productVanId">
                                    </div>
                                    <small class="text-danger">
                                        @error('productVanId')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="productName">Product Name</label>
                                        <input type="text" class="form-control" id="productName"
                                            placeholder="Enter product name..." wire:model="productName">
                                    </div>
                                    <small class="text-danger">
                                        @error('productName')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price">Price</label>
                                        <input type="text" class="form-control" id="price"
                                            placeholder="Enter price..." wire:model="price">
                                    </div>
                                    <small class="text-danger">
                                        @error('price')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku">SKU</label>
                                        <input type="text" class="form-control" id="sku"
                                            placeholder="Enter SKU..." wire:model="sku">
                                    </div>
                                    <small class="text-danger">
                                        @error('sku')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="featured">Featured</label>
                                        <select class="form-control" id="featured" wire:model="featured">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('featured')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discountPercentage">Discount Percentage</label>
                                        <input type="text" class="form-control" id="discountPercentage"
                                            placeholder="Enter discount percentage..." wire:model="discountPercentage">
                                    </div>
                                    <small class="text-danger">
                                        @error('discountPercentage')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="weight">Weight</label>
                                        <input type="number" step="0.01" class="form-control" id="weight"
                                            placeholder="Enter weight..." wire:model="weight">
                                    </div>
                                    <small class="text-danger">
                                        @error('weight')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand">Brand</label>
                                        <input type="text" class="form-control" id="brand"
                                            placeholder="Enter brand..." wire:model="brand">
                                    </div>
                                    <small class="text-danger">
                                        @error('brand')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="size">Size</label>
                                        <input type="text" class="form-control" id="size"
                                            placeholder="Enter size..." wire:model="size">
                                    </div>
                                    <small class="text-danger">
                                        @error('size')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="productStatus">Status</label>
                                        <select class="form-control" id="productStatus" wire:model="productStatus">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="out_of_stock">Out Of Stock</option>
                                            <option value="low_stock">Low Stock</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('productStatus')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact">Contact</label>
                                        <input type="text" class="form-control" id="contact"
                                            placeholder="Enter contact..." wire:model="contact">
                                    </div>
                                    <small class="text-danger">
                                        @error('contact')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="colors">Colors (JSON)</label>
                                        <textarea class="form-control" id="colors" rows="3" placeholder='Example: ["red","blue"]'
                                            wire:model="colors"></textarea>
                                    </div>
                                    <small class="text-danger">
                                        @error('colors')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bike">Bike</label>
                                        <select class="form-control" id="bike" wire:model="bike">
                                            <option value="">Select</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('bike')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="car">Car</label>
                                        <select class="form-control" id="car" wire:model="car">
                                            <option value="">Select</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('car')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="van">Van</label>
                                        <select class="form-control" id="van" wire:model="van">
                                            <option value="">Select</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                    <small class="text-danger">
                                        @error('van')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="d-block">Feature Image</label>
                                        @if ($featureImgUpload)
                                            <img src="{{ $featureImgUpload->temporaryUrl() }}"
                                                alt="Feature image preview" class="img-fluid rounded border mb-2"
                                                style="max-height: 160px;">
                                        @elseif ($featureImg)
                                            <img src="{{ str_contains($featureImg, 'https://') ? $featureImg : config('constants.BUCKET') . $featureImg }}"
                                                alt="Current feature image" class="img-fluid rounded border mb-2"
                                                style="max-height: 160px;">
                                        @endif

                                        <input type="file" class="form-control" id="featureImgUpload"
                                            wire:model="featureImgUpload" accept="image/jpeg,image/png,image/jpg">
                                        <small class="text-muted">Upload only if you want to replace the current
                                            image.</small>
                                    </div>
                                    <small class="text-danger">
                                        @error('featureImgUpload')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="height">Height</label>
                                        <input type="number" step="0.01" class="form-control" id="height"
                                            placeholder="Enter height..." wire:model="height">
                                    </div>
                                    <small class="text-danger">
                                        @error('height')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="width">Width</label>
                                        <input type="number" step="0.01" class="form-control" id="width"
                                            placeholder="Enter width..." wire:model="width">
                                    </div>
                                    <small class="text-danger">
                                        @error('width')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="length">Length</label>
                                        <input type="number" step="0.01" class="form-control" id="length"
                                            placeholder="Enter length..." wire:model="length">
                                    </div>
                                    <small class="text-danger">
                                        @error('length')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="jobReference">Job Reference</label>
                                        <input type="text" class="form-control" id="jobReference"
                                            placeholder="Enter job reference..." wire:model="jobReference">
                                    </div>
                                    <small class="text-danger">
                                        @error('jobReference')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" class="form-control" id="quantity"
                                            placeholder="Enter quantity..." wire:model="quantity">
                                    </div>
                                    <small class="text-danger">
                                        @error('quantity')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="threshold">Threshold</label>
                                        <input type="number" class="form-control" id="threshold"
                                            placeholder="Enter threshold..." wire:model="threshold">
                                    </div>
                                    <small class="text-danger">
                                        @error('threshold')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                            data-bs-dismiss="modal" wire:click="resetComponent">
                            Close
                        </button>
                        <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12 col-xl-2">
                    <h4 class="py-4 my-1 text-site-primary">Vans Inventories</h4>
                </div>
                <div class="col-12 col-xl-2">
                    <div class="input-group py-2 my-2">
                        <input type="text" wire:model.live="search" class="form-control py-2"
                            placeholder="Search by name, qty or threshold...">
                    </div>
                </div>
                <div class="col-12 col-xl-2">
                    <div class="input-group py-2 my-2">
                        <select class="form-control py-2" wire:model.live="vanId">
                            <option value="">Select Van</option>
                            @foreach ($vans as $singleIndex)
                                <option value="{{ $singleIndex->id }}">{{ $singleIndex->number_plate }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-xl-6 d-flex gap-1">
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" onclick="selectAll()"
                        title="Select All">
                        <span class="text-white">All</span>
                    </button>
                    <button type="button" class="btn btn-danger my-3 py-3 w-100" onclick="delVans()"
                        title="Delete Selected">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" title="Import">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </button>
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" title="Export">
                        <i class="fas fa-cloud-download-alt"></i>
                    </button>
                    <div class="dropdown w-100 my-3">
                        <button class="btn btn-site-primary py-3 w-100" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false" title="Add New">
                            <span class="fas fa-plus"></span>
                        </button>
                        <ul class="dropdown-menu w-100 text-start">
                            <li class="dropdown-item cursor-pointer p-3 border-bottom">
                                <a class="dropdown-item" href="{{ route('vans.inventory.add.manually') }}">
                                    Add Manually
                                </a>
                            </li>
                            <li class="dropdown-item cursor-pointer p-3 border-bottom">
                                <a class="dropdown-item"
                                    href="{{ route('vans.inventories.order.online', ['vanId' => (int) $vanId]) }}">
                                    Order Online
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <table class="table text-center table-hover table-responsive-sm border-bottom">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th scope="col">#</th>
                                <th></th>
                                <th scope="col">Name</th>
                                <th scope="col">Price</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Threshold</th>
                                <th scope="col">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $singleIndex)
                                <tr>
                                    <td>{{ $singleIndex->id }}</td>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" title="Select"
                                            id="inventory-{{ $singleIndex->id }}" onclick="event.stopPropagation()">
                                    </td>
                                    <td>{{ $singleIndex->product_name }}</td>
                                    <td>{{ $singleIndex->price }}</td>
                                    <td>{{ $singleIndex->quantity }}</td>
                                    <td>{{ $singleIndex->min_threshold }}</td>
                                    <td>
                                        <a href="{{ route('vans.inventory.edit.manually', ['productId' => $singleIndex->id]) }}" class="btn text-site-primary" title="Edit">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ config('constants.NO_RECORD') }}</td>
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
        </div>
    </div>
</div>
