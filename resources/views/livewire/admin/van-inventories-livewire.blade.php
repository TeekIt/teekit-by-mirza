<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Edit Inventory Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="editVanInventoryModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form>
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Product Inventory</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetComponent"></button>
                            </div>
                            <div class="col-12 my-3">
                                <div class="row">
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="text" class="form-control" id="quantity"
                                                placeholder="Enter quantity..." wire:model="quantity">
                                        </div>
                                        <small class="text-danger">
                                            @error('quantity')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="threshold">Threshold</label>
                                            <input type="text" class="form-control" id="threshold"
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
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                                    data-bs-dismiss="modal" wire:click="resetComponent">
                                    Close
                                </button>
                                <button type="button" class="btn site-primary-yellow-bg rounded-pill px-5 py-2">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12 col-xl-3">
                    <h4 class="py-4 my-1 text-site-primary">Van Inventories</h4>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="input-group py-2 my-2">
                        <input type="text" wire:model.live="search" class="form-control py-2"
                            placeholder="Search by number plate...">
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
                    <a href="{{ route('admin.vans.inventories.search') }}" class="btn btn-site-primary my-3 py-3 w-100" title="Add New">
                        <span class="fas fa-plus"></span>
                    </a>
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
                            @forelse ($data as $inventory)
                                <tr style="cursor: pointer;"
                                    wire:click="renderEditVanInventoryModal({{ $inventory['id'] }})"
                                    data-bs-toggle="modal" data-bs-target="#editVanInventoryModal">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" title="Select"
                                            id="inventory-{{ $inventory['id'] }}" onclick="event.stopPropagation()">
                                    </td>
                                    <td>{{ $inventory['name'] }}</td>
                                    <td>{{ $inventory['price'] }}</td>
                                    <td>{{ $inventory['qty'] }}</td>
                                    <td>{{ $inventory['threshold'] }}</td>
                                    <td>
                                        <button type="button" class="btn text-site-primary" title="Edit product"
                                            onclick="event.stopPropagation()">
                                            <i class="far fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No inventories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
