<div class="container-xxl flex-grow-1 container-p-y">

    @php
        use App\Enums\UserRoleEnum;
        use App\Enums\VanProductStatusEnum;
        use App\Models\User;
    @endphp

    <x-session-messages />

    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-8">
            <div class="card-body p-4 p-md-5">

                <h4 class="text-center text-site-primary fw-bold mb-4">
                    {{ $productId ? 'Edit Product' : 'Add Product' }}
                </h4>

                <form wire:submit="addOrUpdateProduct" enctype="multipart/form-data">

                    {{-- Vans List --}}
                    @if (!empty($vans))
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">
                                    Company Vans<span class="text-danger">*</span>
                                </label>
                                <select class="form-control" wire:model.live="vanId">
                                    <option value="">Select van</option>
                                    @foreach ($vans as $van)
                                        <option value="{{ $van->id }}">{{ $van->number_plate }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger d-block">
                                    @error('vanId')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- Product Name & SKU --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Product Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter product name"
                                wire:model.blur="productName">
                            <small class="text-danger">
                                @error('productName')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">SKU <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter SKU" wire:model.blur="sku">
                            <small class="text-danger">
                                @error('sku')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Category & Stock --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" wire:model.live="categoryId">
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger">
                                @error('categoryId')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Stock<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" placeholder="Enter stock quantity"
                                wire:model.blur="qty" min="0" @if ($this->isPayAsYouGo($type)) disabled @endif>
                            <small class="text-danger">
                                @error('qty')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    @if ($isAuthUserCompany)
                        {{-- Min Threshold --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold">
                                    Min Threshold<span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" placeholder="Enter min threshold"
                                    wire:model.blur="minThreshold" min="0">
                                <small class="text-danger">
                                    @error('minThreshold')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- Price & Discount --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Price<span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" placeholder="Enter price"
                                wire:model.blur="price" min="0">
                            <small class="text-danger">
                                @error('price')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Discount %</label>
                            <input type="number" step="0.01" class="form-control"
                                placeholder="Enter discount percentage" wire:model.blur="discountPercentage"
                                min="0">
                            <small class="text-danger">
                                @error('discountPercentage')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Height & Width --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Height (cm)</label>
                            <input type="number" step="any" class="form-control" placeholder="Enter height"
                                wire:model.blur="height">
                            <small class="text-danger">
                                @error('height')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Width (cm)</label>
                            <input type="number" step="any" class="form-control" placeholder="Enter width"
                                wire:model.blur="width">
                            <small class="text-danger">
                                @error('width')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Length & Weight --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Length (cm)</label>
                            <input type="number" step="any" class="form-control" placeholder="Enter length"
                                wire:model.blur="length">
                            <small class="text-danger">
                                @error('length')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Weight (Kg) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" placeholder="Enter weight"
                                wire:model.blur="weight">
                            <small class="text-danger">
                                @error('weight')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Brand & Contact --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Brand</label>
                            <input type="text" class="form-control" placeholder="Enter brand"
                                wire:model.blur="brand">
                            <small class="text-danger">
                                @error('brand')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold">
                                Contact <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">+44</span>
                                <input type="number" class="form-control" placeholder="Enter 10-digit number"
                                    wire:model.blur="contact"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <small class="text-danger">
                                @error('contact')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            {{-- If Updating Product --}}
                            @if ($isAuthUserCompany && $productId)
                                <label class="form-label text-site-primary fw-semibold">
                                    Status
                                </label>
                                @if ($status == VanProductStatusEnum::IN_STOCK)
                                    <input type="text" class="form-control text-success border-success fw-bold"
                                        value="{{ $status }}" disabled>
                                @endif

                                @if ($status == VanProductStatusEnum::LOW_STOCK)
                                    <input type="text" class="form-control text-warning border-warning fw-bold"
                                        value="{{ $status }}" disabled>
                                @endif

                                @if ($status == VanProductStatusEnum::OUT_OF_STOCK)
                                    <input type="text" class="form-control text-danger border-danger fw-bold"
                                        value="{{ $status }}" disabled>
                                @endif
                            @elseif($isAuthUserParentSeller)
                                <label class="form-label text-site-primary fw-semibold">
                                    Status<span class="text-danger">*</span>
                                </label>
                                <select class="form-control" wire:model.live="status">
                                    <option value="">Select status</option>
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            @endif
                            <small class="text-danger">
                                @error('status')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Colors --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-site-primary fw-semibold">Colors</label>
                            <select class="form-control" wire:model.live="colors" multiple size="5">
                                @foreach ($commonColors as $color)
                                    <option value="{{ $color }}">{{ $color }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl / Cmd to select multiple colors.</small>
                            <small class="text-danger d-block">
                                @error('colors')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    {{-- Vehicle Type --}}
                    @if ($isAuthUserParentSeller)
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">
                                    Vehicle Type <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="bike"
                                            wire:model.live="vehicle" id="bike">
                                        <label class="form-check-label" for="bike">Cycle / Bike</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="car"
                                            wire:model.live="vehicle" id="car">
                                        <label class="form-check-label" for="car">Car</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="van"
                                            wire:model.live="vehicle" id="van">
                                        <label class="form-check-label" for="van">Van</label>
                                    </div>
                                </div>
                                <small class="text-danger">
                                    @error('vehicle')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- Feature Image --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-site-primary fw-semibold d-block">
                                Feature Image {{ $productId ? '' : '*' }}
                            </label>

                            {{-- Live preview of new upload --}}
                            @if ($featureImgUpload)
                                <img src="{{ $featureImgUpload->temporaryUrl() }}" alt="Feature image preview"
                                    class="img-fluid rounded border mb-2" style="max-height: 140px;">
                            @elseif ($featureImg)
                                <img src="{{ str_contains($featureImg, 'https://') ? $featureImg : config('constants.BUCKET') . $featureImg }}"
                                    alt="Current feature image" class="img-fluid rounded border mb-2"
                                    style="max-height: 140px;">
                            @endif

                            <input type="file" class="form-control" accept="image/jpeg,image/png,image/jpg"
                                wire:model="featureImgUpload">
                            <small class="text-muted">
                                {{ $productId ? 'Upload only if you want to replace the current image.' : 'JPEG or PNG, max 1 MB.' }}
                            </small>
                            <small class="text-danger d-block">
                                @error('featureImgUpload')
                                    {{ $message }}
                                @enderror
                            </small>
                            <div wire:loading wire:target="featureImgUpload" class="text-muted mt-1">
                                <span class="spinner-border spinner-border-sm"></span> Uploading...
                            </div>
                        </div>

                        {{-- Gallery --}}
                        @if (User::getAuthUser()->role_id == UserRoleEnum::SELLER->value)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">Image Gallery</label>

                                {{-- Existing gallery images --}}
                                @if (!empty($existingImages))
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach ($existingImages as $image)
                                            <div class="position-relative">
                                                <img src="{{ str_contains($image['product_image'], 'https://') ? $image['product_image'] : config('constants.BUCKET') . $image['product_image'] }}"
                                                    alt="Gallery image" class="rounded border"
                                                    style="height: 70px; width: 70px; object-fit: cover;">
                                                <button type="button"
                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0"
                                                    style="width:18px;height:18px;font-size:10px;line-height:1;"
                                                    wire:click="removeGalleryImage({{ $image['id'] }})"
                                                    title="Remove">
                                                    &times;
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <input type="file" class="form-control" accept="image/jpeg,image/png,image/jpg"
                                    wire:model="galleryUploads" multiple>
                                <small class="text-muted">You can select multiple images.</small>
                                <small class="text-danger d-block">
                                    @error('galleryUploads.*')
                                        {{ $message }}
                                    @enderror
                                </small>
                                <div wire:loading wire:target="galleryUploads" class="text-muted mt-1">
                                    <span class="spinner-border spinner-border-sm"></span> Uploading...
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-6 offset-md-3 text-center mt-2">
                            <button type="submit"
                                class="btn site-primary-yellow-bg rounded-pill px-5 py-2 fw-semibold w-100"
                                wire:target="addOrUpdateProduct" wire:loading.class="btn-dark"
                                wire:loading.class.remove="btn-warning" wire:loading.attr="disabled">
                                <span wire:target="addOrUpdateProduct" wire:loading.remove>
                                    {{ $productId ? 'Update' : 'Add' }}
                                </span>
                                <span wire:target="addOrUpdateProduct" wire:loading>
                                    <span class="spinner-border spinner-border-sm text-dark" role="status"
                                        aria-hidden="true"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        input:disabled {
            background-color: #e1e1e1 !important;
        }
    </style>
</div>
