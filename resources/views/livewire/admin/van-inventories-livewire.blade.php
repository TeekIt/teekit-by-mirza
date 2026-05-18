<div class="container-xxl flex-grow-1 container-p-y">

    @php
        use Illuminate\Support\Str;
    @endphp

    <x-session-messages />

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12 col-xl-2">
                    <h4 class="py-4 my-1 text-site-primary">Vans Inventories</h4>
                </div>
                <div class="col-12 col-xl-2">
                    <div class="input-group py-2 my-2">
                        <input type="text" wire:model.live="search" class="form-control py-2"
                            placeholder="Search by name, qty or threshold..." title="Search by name, qty or threshold...">
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
                        <ul class="dropdown-menu  text-start">
                            <li class="dropdown-item cursor-pointer p-3 border-bottom">
                                <a class="dropdown-item" href="{{ route('vans.inventory.add.manually') }}">
                                    Add Manually
                                </a>
                            </li>
                            <li class="dropdown-item cursor-pointer p-3 border-bottom">
                                <a class="dropdown-item"
                                    href="{{ route('vans.inventories.order.pay.as.you.go') }}">
                                    Add Pay As You Go
                                </a>
                            </li>
                            <li class="dropdown-item cursor-pointer p-3 border-bottom">
                                <a class="dropdown-item"
                                    href="{{ route('vans.inventories.order.online') }}">
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
                    <table class="table table-hover table-responsive-sm border-bottom">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th scope="col">#</th>
                                <th></th>
                                <th scope="col">Name</th>
                                <th scope="col">Price</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Threshold</th>
                                <th scope="col">Type</th>
                                <th scope="col" class="text-center">Options</th>
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
                                    <td title="{{ $singleIndex->product_name }}">
                                        {{ Str::limit($singleIndex->product_name, 30) }}
                                    </td>
                                    <td>{{ $singleIndex->price }}</td>
                                    <td>{{ $singleIndex->quantity }}</td>
                                    <td>{{ $singleIndex->min_threshold }}</td>
                                    <td>{{ $singleIndex->type }}</td>
                                    <td class="text-center">
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
