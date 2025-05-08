@php
    use App\Products;
@endphp

@extends('layouts.shopkeeper.app')

@section('styles')
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3a4b83;
        }
    </style>
@endsection

@section('content')
    <div class="content">

        <x-session-messages />

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="offset-xl-2 col-lg-12 col-xl-8 pb-4">
                        <div class="card-body">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="text-center text-site-primary">Edit Product</h4>
                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{ route('seller.edit.inventory', $inventory->id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Title*</label>
                                                            <input type="text" class="form-control" name="product_name"
                                                                placeholder="Title*" required
                                                                value="{{ $inventory->product_name }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">SKU*</label>
                                                            <input type="text" class="form-control" name="sku"
                                                                placeholder="" required value="{{ $inventory->sku }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Category</label>
                                                            <select class="form-control" required name="category_id">
                                                                <option value="">Category*</option>
                                                                @foreach ($categories as $cat)
                                                                    <option
                                                                        @if ($cat->id == $inventory->category_id) selected @endif
                                                                        value="{{ $cat->id }}">
                                                                        {{ $cat->category_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Stock *</label>
                                                            <input type="number" class="form-control" name="qty"
                                                                placeholder="Stock*" required
                                                                value="{{ $inventory->qty[0]->qty }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">
                                                                Price <span class="text-secondary">(Excluding VAT)</span>
                                                            </label>
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="price" placeholder="Price*" required
                                                                value="{{ $inventory->price }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">
                                                                Discount Percentage
                                                                <span class="text-secondary">
                                                                    (Excluding VAT)
                                                                </span>
                                                            </label>
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="discount_percentage" placeholder="Discounted Price*"
                                                                value="{{ $inventory->discount_percentage }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Height</label>
                                                            <input type="number" step="any" class="form-control"
                                                                name="height" placeholder="Height (cm)"
                                                                value="{{ $inventory->height }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Width</label>
                                                            <input type="number" step="any" class="form-control"
                                                                name="width" placeholder="Width (cm)"
                                                                value="{{ $inventory->width }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Length</label>
                                                            <input type="number" step="any" class="form-control"
                                                                name="length" placeholder="Length (cm)"
                                                                value="{{ $inventory->length }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Weight</label>
                                                            <input type="number" step="any" class="form-control"
                                                                name="weight" placeholder="Weight (Kg)"
                                                                value="{{ $inventory->weight }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Brand</label>
                                                            <input type="text" class="form-control" name="brand"
                                                                placeholder="Brand" value="{{ $inventory->brand }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Status</label>
                                                            <select class="form-control" name="status" required>
                                                                <option value=""
                                                                    @if (!isset($inventory->status)) selected @endif>
                                                                    Status*
                                                                </option>
                                                                <option @if (isset($inventory->status) && $inventory->status->value == 1) selected @endif
                                                                    value="1">
                                                                    Enabled
                                                                </option>
                                                                <option @if (isset($inventory->status) && $inventory->status->value == 0) selected @endif
                                                                    value="0">
                                                                    Disabled
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="text-left d-block">Contact*</label>
                                                            <div class="form-group row">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">+44</span>
                                                                    <input type="number" class="form-control"
                                                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                                                        placeholder="Contact*" name="contact"
                                                                        value="{{ str_replace('+44', '', $inventory->contact) }}"
                                                                        required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-left">
                                                        @php
                                                            if ($inventory->colors) {
                                                                $colors = array_keys(
                                                                    json_decode($inventory->colors, true),
                                                                );
                                                            }
                                                        @endphp
                                                        <select class="colors form-control" name="colors[]"
                                                            multiple="multiple">
                                                            @foreach (Products::getCommonColors() as $singleColor)
                                                                <option value="{{ $singleColor }}"
                                                                    @isset($colors) 
                                                                        @if (in_array($singleColor, $colors)) selected @endif
                                                                    @endisset>
                                                                    {{ $singleColor }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <div class="form-group mt-3">
                                                            <label class="text-left d-block">Edit Feature Image</label>
                                                            <div>
                                                                <input type="file" accept="image/*"
                                                                    name="feature_img">
                                                            </div>
                                                            <div class="img-to-del d-inline-block position-relative"
                                                                style="max-width: 150px">
                                                                @php
                                                                    if (
                                                                        str_contains(
                                                                            $inventory->feature_img,
                                                                            'https://',
                                                                        )
                                                                    ) {
                                                                        $featureImageUrl = $inventory->feature_img;
                                                                    } else {
                                                                        $featureImageUrl =
                                                                            config('constants.BUCKET') .
                                                                            $inventory->feature_img;
                                                                    }
                                                                @endphp
                                                                <img class="img-fluid" src="{{ $featureImageUrl }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <div class="row mt-3">
                                                            <div class="col-md-12 text-left">
                                                                <label class="text-left d-block">
                                                                    Edit Image Gallery
                                                                </label>
                                                                <div>
                                                                    <input type="file" accept="image/*"
                                                                        name="gallery[]" multiple>
                                                                </div>
                                                                <div class="img-to-del-container">
                                                                    @if ($inventory->images)
                                                                        @foreach ($inventory->images as $img)
                                                                            <div class="img-to-del d-inline-block position-relative"
                                                                                style="max-width: 80px">
                                                                                <a href="{{ route('seller.delete.img', ['imageId' => $img->id]) }}"
                                                                                    class="text-sm position-absolute">
                                                                                    <i
                                                                                        class="fas fa-trash text-danger"></i>
                                                                                </a>
                                                                                <img class="img-fluid p-2"
                                                                                    src="{{ config('constants.BUCKET') . $img->product_image }}">
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <p>
                                                            <input @if ($inventory->bike == 1) checked @endif
                                                                type="radio" name="vehicle" value="bike" required>
                                                            Cycle/Bike &emsp;
                                                            <input @if ($inventory->car == 1) checked @endif
                                                                type="radio" name="vehicle" value="car" required>
                                                            Car &emsp;
                                                            <input @if ($inventory->van == 1) checked @endif
                                                                type="radio" name="vehicle" value="van" required>
                                                            Van &emsp;
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <button style="background: #ffcf42;color:black;font-weight: 600"
                                                            class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                            type="submit">
                                                            Update
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <style>
        .card-body {
            padding: 30px 50px !important;
        }
    </style>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.colors').select2({
                placeholder: "Select Colors",
                allowClear: true
            });
        });
    </script>
@endsection
