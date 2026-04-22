<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-8">
                <div class="card-body p-4 p-md-5">

                    <h4 class="text-center text-site-primary fw-bold mb-4">
                        {{ $productId ? 'Edit Product' : 'Add Product' }}
                    </h4>

                    <form wire:submit="save">

                        {{-- Row 1: Product Name & SKU --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Enter product name"
                                    wire:model.blur="productName">
                                <small class="text-danger">@error('productName') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Enter SKU"
                                    wire:model.blur="sku">
                                <small class="text-danger">@error('sku') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 2: Category & Stock --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.live="categoryId">
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger">@error('categoryId') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" placeholder="Enter stock quantity"
                                    wire:model.blur="qty" min="0">
                                <small class="text-danger">@error('qty') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 3: Price & Discount --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" placeholder="Enter price"
                                    wire:model.blur="price" min="0">
                                <small class="text-danger">@error('price') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Discount %</label>
                                <input type="number" step="0.01" class="form-control" placeholder="Enter discount percentage"
                                    wire:model.blur="discountPercentage" min="0">
                                <small class="text-danger">@error('discountPercentage') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 4: Height & Width --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Height (cm)</label>
                                <input type="number" step="any" class="form-control" placeholder="Enter height"
                                    wire:model.blur="height">
                                <small class="text-danger">@error('height') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Width (cm)</label>
                                <input type="number" step="any" class="form-control" placeholder="Enter width"
                                    wire:model.blur="width">
                                <small class="text-danger">@error('width') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 5: Length & Weight --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Length (cm)</label>
                                <input type="number" step="any" class="form-control" placeholder="Enter length"
                                    wire:model.blur="length">
                                <small class="text-danger">@error('length') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Weight (Kg) <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" placeholder="Enter weight"
                                    wire:model.blur="weight">
                                <small class="text-danger">@error('weight') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 6: Brand --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Brand</label>
                                <input type="text" class="form-control" placeholder="Enter brand"
                                    wire:model.blur="brand">
                                <small class="text-danger">@error('brand') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 7: Status & Contact --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.live="status">
                                    <option value="">Select status</option>
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                                <small class="text-danger">@error('status') {{ $message }} @enderror</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Contact <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+44</span>
                                    <input type="number" class="form-control" placeholder="Enter 10-digit number"
                                        wire:model.blur="contact"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <small class="text-danger">@error('contact') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 8: Colors --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold">Colors</label>
                                <select class="form-control" wire:model.live="colors" multiple size="5">
                                    @foreach ($commonColors as $color)
                                        <option value="{{ $color }}">{{ $color }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl / Cmd to select multiple colors.</small>
                                <small class="text-danger d-block">@error('colors') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 9: Vehicle Type --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">Vehicle Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="bike"
                                            wire:model.live="vehicle" id="vehicleBike">
                                        <label class="form-check-label" for="vehicleBike">Cycle / Bike</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="car"
                                            wire:model.live="vehicle" id="vehicleCar">
                                        <label class="form-check-label" for="vehicleCar">Car</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="van"
                                            wire:model.live="vehicle" id="vehicleVan">
                                        <label class="form-check-label" for="vehicleVan">Van</label>
                                    </div>
                                </div>
                                <small class="text-danger">@error('vehicle') {{ $message }} @enderror</small>
                            </div>
                        </div>

                        {{-- Row 10: Feature Image --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">
                                    Feature Image {{ $productId ? '' : '*' }}
                                </label>

                                {{-- Live preview of new upload --}}
                                @if ($featureImgUpload)
                                    <img src="{{ $featureImgUpload->temporaryUrl() }}"
                                        alt="Feature image preview"
                                        class="img-fluid rounded border mb-2"
                                        style="max-height: 140px;">
                                @elseif ($featureImg)
                                    <img src="{{ str_contains($featureImg, 'https://') ? $featureImg : config('constants.BUCKET') . $featureImg }}"
                                        alt="Current feature image"
                                        class="img-fluid rounded border mb-2"
                                        style="max-height: 140px;">
                                @endif

                                <input type="file" class="form-control" accept="image/jpeg,image/png,image/jpg"
                                    wire:model="featureImgUpload">
                                <small class="text-muted">
                                    {{ $productId ? 'Upload only if you want to replace the current image.' : 'JPEG or PNG, max 1 MB.' }}
                                </small>
                                <small class="text-danger d-block">
                                    @error('featureImgUpload') {{ $message }} @enderror
                                </small>
                                <div wire:loading wire:target="featureImgUpload" class="text-muted mt-1">
                                    <span class="spinner-border spinner-border-sm"></span> Uploading...
                                </div>
                            </div>

                            {{-- Gallery --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-site-primary fw-semibold d-block">Image Gallery</label>

                                {{-- Existing gallery images --}}
                                @if (!empty($existingImages))
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach ($existingImages as $image)
                                            <div class="position-relative">
                                                <img src="{{ str_contains($image['product_image'], 'https://') ? $image['product_image'] : config('constants.BUCKET') . $image['product_image'] }}"
                                                    alt="Gallery image"
                                                    class="rounded border"
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
                                    @error('galleryUploads.*') {{ $message }} @enderror
                                </small>
                                <div wire:loading wire:target="galleryUploads" class="text-muted mt-1">
                                    <span class="spinner-border spinner-border-sm"></span> Uploading...
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="row">
                            <div class="col-md-6 offset-md-3 text-center mt-2">
                                <button type="submit"
                                    class="btn site-primary-yellow-bg rounded-pill px-5 py-2 fw-semibold w-100"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="btn-dark"
                                    wire:target="save">
                                    <span wire:loading wire:target="save"
                                        class="spinner-border spinner-border-sm me-1"></span>
                                    {{ $productId ? 'Update Product' : 'Add Product' }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
        </div>
    </div>

</div>

