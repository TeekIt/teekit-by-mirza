<div class="col-11">

    {{-- ************************************ Add Categories Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="addSubCategoryModal{{ $category->id }}" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form wire:submit.prevent="addSubCategory">
                            {{ csrf_field() }}
                            <div class="modal-header">
                                <h5 class="modal-title">Add Sub Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetComponent"></button>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control"
                                            placeholder="Enter sub category name..." wire:model.defer="name">
                                    </div>
                                    <small class="text-danger">
                                        @error('name')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer hidden">
                                <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                                    data-bs-dismiss="modal" wire:click="resetComponent">
                                    Close
                                </button>
                                <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
                                    wire:target="addSubCategory" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled">
                                    <span wire:target="addSubCategory" wire:loading.remove>
                                        Add
                                    </span>
                                    <span wire:target="addSubCategory" wire:loading>
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

    {{-- ************************************ Edit Categories Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="editSubCategoryModal{{ $category->id }}" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form wire:submit.prevent>
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetComponent"></button>
                            </div>
                            <div class="col-12 mb-3">
                                <label>Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" wire:model.defer="name"
                                        placeholder="Enter sub category name...">
                                    <button type="button" class="btn btn-site-primary"
                                        wire:click="updateSubCategoryName" wire:loading.class="btn-dark"
                                        wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled"
                                        wire:target="updateSubCategoryName">
                                        <span wire:loading.remove wire:target="updateSubCategoryName">Update</span>
                                        <span wire:loading wire:target="updateSubCategoryName">
                                            <span class="spinner-border spinner-border-sm text-light"
                                                role="status"></span>
                                        </span>
                                    </button>
                                </div>
                                <small class="text-danger">
                                    @error('name')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                                    data-bs-dismiss="modal" wire:click="resetComponent">
                                    Close
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
            <div class="row mb-2">
                <div class="col-12 col-sm-6 col-md-7 col-xl-9">
                    <!-- For maintaining left space -->
                </div>
                <div class="col-12 col-md-5 col-xl-3 d-flex gap-2">
                    <button type="button" class="btn btn-danger my-3 py-3 w-100" onclick="delSubCategories()"
                        title="Delete Selected">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button data-bs-toggle="modal" data-bs-target="#addSubCategoryModal{{ $category->id }}"
                        class="btn btn-site-primary my-3 py-3 w-100" title="Add New">
                        <span class="fas fa-plus"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <table class="table text-center table-hover table-responsive-sm border-bottom">
            <thead>
                <tr class="bg-primary text-white">
                    <th scope="col">#</th>
                    <th></th>
                    <th scope="col">Name</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Options</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($category->subCategories as $subCategory)
                    <tr>
                        <td>{{ $subCategory->id }}</td>
                        <td>
                            <input type="checkbox" class="select-checkbox" title="Select"
                                id="{{ $subCategory->id }}">
                        </td>
                        <td>{{ $subCategory->name }}</td>
                        <td>{{ $subCategory->created_at }}</td>
                        <td>
                            <button data-bs-toggle="modal" data-bs-target="#editSubCategoryModal{{ $category->id }}"
                                wire:click="renderEditSubCategoryModal({{ $subCategory->id }})"
                                class="btn text-site-primary">
                                <i class="far fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ config('constants.NO_RECORD') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- <div>
            {{ $subCategories->links() }}
        </div> --}}
    </div>
</div>
