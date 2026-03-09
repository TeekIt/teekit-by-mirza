<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Add Van Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="addVanModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form wire:submit="addVan">
                            {{ csrf_field() }}
                            <div class="modal-header">
                                <h5 class="modal-title">Add Van</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetComponent"></button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="username"
                                            placeholder="Enter username..." wire:model="username">
                                    </div>
                                    <small class="text-danger">
                                        @error('username')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="operative"
                                            placeholder="Enter operative name..." wire:model="operative">
                                    </div>
                                    <small class="text-danger">
                                        @error('operative')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="numberPlate"
                                            placeholder="Enter number plate..." wire:model="numberPlate">
                                    </div>
                                    <small class="text-danger">
                                        @error('numberPlate')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="number" step="0.01" class="form-control" id="payload"
                                            placeholder="Enter payload in kg..." wire:model="payload">
                                    </div>
                                    <small class="text-danger">
                                        @error('payload')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="number" step="0.01" class="form-control"
                                            placeholder="Width (m)" wire:model="width">
                                    </div>
                                    <small class="text-danger">
                                        @error('width')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="number" step="0.01" class="form-control"
                                            placeholder="Height (m)" wire:model="height">
                                    </div>
                                    <small class="text-danger">
                                        @error('height')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="number" step="0.01" class="form-control"
                                            placeholder="Length (m)" wire:model="length">
                                    </div>
                                     <small class="text-danger">
                                        @error('length')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="password" class="form-control" id="password"
                                            placeholder="Enter password..." wire:model="password">
                                    </div>
                                    <small class="text-danger">
                                        @error('password')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                                    data-bs-dismiss="modal" wire:click="resetComponent">
                                    Close
                                </button>
                                <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
                                    wire:target="addVan" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled">
                                    <span wire:target="addVan" wire:loading.remove>
                                        Add
                                    </span>
                                    <span wire:target="addVan" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ************************************ Edit Van Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="editVanModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form wire:submit="updateVan">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Van</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetComponent"></button>
                            </div>
                            <div class="col-12 my-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username"
                                                placeholder="Enter username..." wire:model="username">
                                        </div>
                                        <small class="text-danger">
                                            @error('username')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="operative">Operative (Driver)</label>
                                            <input type="text" class="form-control" id="operative"
                                                placeholder="Enter operative name..." wire:model="operative">
                                        </div>
                                        <small class="text-danger">
                                            @error('operative')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="numberPlate">Number Plate</label>
                                            <input type="text" class="form-control" id="numberPlate"
                                                placeholder="Enter number plate..." wire:model="numberPlate">
                                        </div>
                                        <small class="text-danger">
                                            @error('numberPlate')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="payload">Payload (kg)</label>
                                            <input type="number" step="0.01" class="form-control" id="payload"
                                                placeholder="Enter payload in kg..." wire:model="payload">
                                        </div>
                                        <small class="text-danger">
                                            @error('payload')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" step="0.01" class="form-control"
                                                placeholder="Width (m)" wire:model="dimensionWidth">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" step="0.01" class="form-control"
                                                placeholder="Height (m)" wire:model="dimensionHeight">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" step="0.01" class="form-control"
                                                placeholder="Length (m)" wire:model="dimensionLength">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <small class="text-danger">
                                            @error('dimensionWidth')
                                                {{ $message }}
                                            @enderror
                                            @error('dimensionHeight')
                                                {{ $message }}
                                            @enderror
                                            @error('dimensionLength')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password"
                                                placeholder="Enter password..." wire:model="password">
                                        </div>
                                        <small class="text-danger">
                                            @error('password')
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
                                <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
                                    wire:target="updateVan" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled">
                                    <span wire:target="updateVan" wire:loading.remove>
                                        Update
                                    </span>
                                    <span wire:target="updateVan" wire:loading>
                                        <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                                    </span>
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
                    <h4 class="py-4 my-1 text-site-primary">Vans</h4>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="input-group py-2 my-2">
                        <input type="text" wire:model.live="search" class="form-control py-2"
                            placeholder="Search by number plate...">
                    </div>
                </div>
                <div class="col-12 col-xl-6 d-flex gap-1">
                    <button type="button" class="btn btn-success my-3 py-3 w-100" onclick="selectAll()"
                        title="Select All">
                        <span class="text-white">All</span>
                    </button>
                    <button type="button" class="btn btn-success my-3 py-3 w-100" title="Import">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </button>
                    <button type="button" class="btn btn-success my-3 py-3 w-100" title="Export">
                        <i class="fas fa-cloud-download-alt"></i>
                    </button>
                    <button type="button" class="btn btn-danger my-3 py-3 w-100" onclick="delVans()"
                        title="Delete Selected">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" data-bs-toggle="modal"
                        data-bs-target="#addVanModal" title="Add New">
                        <span class="fas fa-plus"></span>
                    </button>
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
                                <th scope="col">Username</th>
                                <th scope="col">Operative (Driver)</th>
                                <th scope="col">Number Plate</th>
                                <th scope="col">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $singleIndex)
                                <a href="{{ route('admin.sellers.parent') }}">
                                    <tr wire:click="renderEditVanModal({{ $singleIndex->id }})" data-bs-toggle="modal"
                                        data-bs-target="#editVanModal">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <input type="checkbox" class="select-checkbox" title="Select"
                                                id="van-{{ $singleIndex->id }}" onclick="event.stopPropagation()">
                                        </td>
                                        <td>{{ $singleIndex->username }}</td>
                                        <td>{{ $singleIndex->operative }}</td>
                                        <td>{{ $singleIndex->number_plate }}</td>
                                        <td>
                                            <button type="button" class="btn text-site-primary"
                                                onclick="event.stopPropagation()">
                                                <i class="far fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </a>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ config('constants.NO_RECORD') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
