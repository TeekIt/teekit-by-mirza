<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Add Categories Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form wire:submit.prevent="addCategory">
                            {{ csrf_field() }}
                            <div class="modal-header">
                                <h5 class="modal-title">Add Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetModal"></button>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="file" class="form-control" accept="image/*"
                                            wire:model.defer="image">
                                    </div>
                                    <small class="text-danger">
                                        @error('image')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Enter category name..."
                                            wire:model.defer="name">
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
                                    data-bs-dismiss="modal" wire:click="resetModal">
                                    Close
                                </button>
                                <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
                                    wire:target="addCategory" wire:loading.class="btn-dark"
                                    wire:loading.class.remove="site-primary-yellow-bg" wire:loading.attr="disabled">
                                    <span wire:target="addCategory" wire:loading.remove>
                                        Add
                                    </span>
                                    <span wire:target="addCategory" wire:loading>
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
    <div wire:ignore.self class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <form>
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="resetModal"></button>
                            </div>
                            <div class="col-12 my-3">
                                <div class="d-flex justify-content-center">
                                    <img class="w-25" src="{{ config('constants.BUCKET') . $image }}">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label>Image</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" accept="image/*"
                                        wire:model.defer="image">
                                    <button type="button" class="btn btn-site-primary" wire:click="updateCategoryImage"
                                        wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                                        wire:loading.attr="disabled" wire:target="updateCategoryImage">
                                        <span wire:loading.remove wire:target="updateCategoryImage">Update</span>
                                        <span wire:loading wire:target="updateCategoryImage">
                                            <span class="spinner-border spinner-border-sm text-light"
                                                role="status"></span>
                                        </span>
                                    </button>
                                </div>
                                <small class="text-danger">
                                    @error('image')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                            <div class="col-12 mb-3">
                                <label>Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" wire:model.defer="name"
                                        placeholder="Enter category name...">
                                    <button type="button" class="btn btn-site-primary" wire:click="updateCategoryName"
                                        wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                                        wire:loading.attr="disabled" wire:target="updateCategoryName">
                                        <span wire:loading.remove wire:target="updateCategoryName">Update</span>
                                        <span wire:loading wire:target="updateCategoryName">
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
                                    data-bs-dismiss="modal" wire:click="resetModal">
                                    Close
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 col-sm-6 col-md-7 col-xl-9">
                    <h4 class="py-4 my-1 text-site-primary">Categories</h4>
                </div>
                <div class="col-12 col-md-5 col-xl-3 d-flex gap-2">
                    <button type="button" class="btn btn-success my-3 py-3 w-100" onclick="selectAll()"
                        title="Select All">
                        <span class="text-white">All</span>
                    </button>
                    <button type="button" class="btn btn-danger my-3 py-3 w-100" onclick="delCategories()"
                        title="Delete Selected">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button class="btn btn-site-primary my-3 py-3 w-100" data-bs-toggle="modal"
                        data-bs-target="#addCategoryModal" title="Add New">
                        <span class="fas fa-plus"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 ">
                    <table class="table text-center table-hover table-responsive-sm border-bottom">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th scope="col">#</th>
                                <th></th>
                                <th scope="col">Icon</th>
                                <th scope="col">Name</th>
                                <th scope="col">Created At</th>
                                <th scope="col">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" title="Select"
                                            id="{{ $category->id }}">
                                    </td>
                                    <td class="col-1">
                                        <span class="img-container">
                                            <img class="d-block m-auto"
                                                src="{{ config('constants.BUCKET') . $category->category_image }}">
                                        </span>
                                    </td>
                                    <td>{{ $category->category_name }}</td>
                                    <td>{{ $category->created_at }}</td>
                                    <td>
                                        <button data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                            wire:click="renderEditCategoryModal({{ $category->id }})"
                                            class="btn text-site-primary">
                                            <i class="far fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse{{ $category->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapse{{ $category->id }}">
                                                        Sub Categories
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ $category->id }}"
                                                    class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                                    data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div
                                                            class="d-flex align-items-center flex-column border border-danger">

                                                            <livewire:admin.sub-categories-livewire :subCategories="$category->subCategories"
                                                                wire:key="sub-categories-{{ $category->id }}" />

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ config('constants.NO_RECORD') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div>
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->
</div>
