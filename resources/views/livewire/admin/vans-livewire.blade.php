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
                                        <input type="text" class="form-control" placeholder="Enter username..."
                                            wire:model="userName">
                                    </div>
                                    <small class="text-danger">
                                        @error('userName')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Enter operative name..."
                                            wire:model="operative">
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
                                        <input type="text" class="form-control" placeholder="Enter number plate..."
                                            wire:model="numberPlate">
                                    </div>
                                    <small class="text-danger">
                                        @error('numberPlate')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="number" step="0.01" class="form-control"
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
                                        <input type="text" class="form-control" placeholder="Enter password..."
                                            wire:model="password">
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
                                            <label for="userName">Username</label>
                                            <input type="text" class="form-control" id="userName"
                                                wire:model="userName">
                                        </div>
                                        <small class="text-danger">
                                            @error('userName')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="operative">Operative (Driver)</label>
                                            <input type="text" class="form-control" id="operative"
                                                wire:model="operative">
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
                                                wire:model="numberPlate">
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
                                                wire:model="payload">
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
                                            <label for="width">Width (m)</label>
                                            <input type="number" step="0.01" class="form-control" id="width"
                                                wire:model="width">
                                        </div>
                                        <small class="text-danger">
                                            @error('width')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="height">Height (m)</label>
                                            <input type="number" step="0.01" class="form-control" id="height"
                                                wire:model="height">
                                        </div>
                                        <small class="text-danger">
                                            @error('height')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="length">Length (m)</label>
                                            <input type="number" step="0.01" class="form-control" id="length"
                                                wire:model="length">
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
                                            <label for="password">New Password</label>
                                            <input type="text" class="form-control" id="password"
                                                wire:model="password">
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
                                        <span class="spinner-border spinner-border-sm text-light"
                                            role="status"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

     {{-- ************************************ Import Vans Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="importVansModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit="importVans">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Vans</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="$set('excelFile', null)"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="jumbotron bg-light p-4 rounded mb-3">
                            <p class="mb-0">Please! download the following template file &amp; only import data according to it</p>
                        </div>
                        <a href="{{ asset('templates/vans-import-template.csv') }}" class="d-inline-block mb-3 text-primary" download>
                            <u>Download Template</u>
                        </a>
                        <div class="form-group text-start">
                            <label for="excelFile">Upload File</label>
                            <input type="file" id="excelFile" class="form-control" accept=".csv" wire:model="excelFile">
                            <small class="text-danger">
                                @error('excelFile')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-5 py-2" data-bs-dismiss="modal"
                            wire:click="$set('excelFile', null)">
                            Close
                        </button>
                        <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
                            wire:target="importVans" wire:loading.class="btn-dark"
                            wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled">
                            <span wire:target="importVans" wire:loading.remove>
                                Import
                            </span>
                            <span wire:target="importVans" wire:loading>
                                <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-12 col-xl-2">
                    <h4 class="py-4 my-1 text-site-primary">Vans</h4>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="input-group py-2 my-2">
                        <input type="text" wire:model.live="search" class="form-control py-2"
                            placeholder="Search by username, operative or number...">
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
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" data-bs-toggle="modal"
                        data-bs-target="#importVansModal" title="Import">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </button>
                    <button type="button" class="btn btn-site-primary my-3 py-3 w-100" wire:click="exportVans"
                        wire:target="exportVans" wire:loading.attr="disabled" title="Export">
                        <span wire:target="exportVans" wire:loading.remove>
                            <i class="fas fa-cloud-download-alt"></i>
                        </span>
                        <span wire:target="exportVans" wire:loading>
                            <span class="spinner-border spinner-border-sm text-light" role="status"></span>
                        </span>
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
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <input type="checkbox" class="select-checkbox" title="Select"
                                                id="van-{{ $singleIndex->id }}" onclick="event.stopPropagation()">
                                        </td>
                                        <td>{{ $singleIndex->user_name }}</td>
                                        <td>{{ $singleIndex->operative }}</td>
                                        <td>{{ $singleIndex->number_plate }}</td>
                                        <td>
                                            <button type="button" data-bs-toggle="modal"
                                                data-bs-target="#editVanModal"
                                                wire:click="renderEditVanModal({{ $singleIndex->id }})"
                                                class="btn text-site-primary">
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
