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
                    <div class="offset-xl-2 col-lg-12 col-xl-8 py-4">
                        <div class="card-body">
                            <div class="d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="text-center text-primary">Add Product</h4>
                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{ route('seller.add.single.inventory') }}" method="POST"
                                                enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                name="product_name" placeholder="Title*"
                                                                value="{{ $inventory->product_name }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="sku"
                                                                placeholder="SKU*" value="{{ $inventory->sku }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control" name="category_id" required>
                                                                <option value="">Category*</option>
                                                                @foreach ($categories as $cat)
                                                                    <option
                                                                        @if ($cat->id == $inventory->category_id) selected @endif
                                                                        value="{{ $cat->id }}">
                                                                        {{ $cat->category_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" name="qty"
                                                                placeholder="Stock*" value="{{ $inventory->qty }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="price" placeholder="Price*"
                                                                value="{{ $inventory->price }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="discount_percentage" placeholder="Discount %"
                                                                value="{{ $inventory->discount_percentage }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="height" placeholder="Height (cm)"
                                                                value="{{ $inventory->height }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="width" placeholder="Width (cm)"
                                                                value="{{ $inventory->width }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="length" placeholder="Length (cm)"
                                                                value="{{ $inventory->length }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="weight" placeholder="Weight (Kg)*"
                                                                value="{{ $inventory->weight }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="brand"
                                                                placeholder="Brand" value="{{ $inventory->brand }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control" name="status" required>
                                                                <option value=""
                                                                    @if (!isset($inventory->status)) selected @endif>
                                                                    Status*
                                                                </option>
                                                                <option
                                                                    @if (isset($inventory->status) && $inventory->status == 1) selected @endif
                                                                    value="1">
                                                                    Enabled
                                                                </option>
                                                                <option
                                                                    @if (isset($inventory->status) && $inventory->status == 0) selected @endif
                                                                    value="0">
                                                                    Disabled
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">+44</span>
                                                                <input type="number" class="form-control"
                                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                                                    placeholder="Contact*" id="contact"
                                                                    name="contact" value="{{ $inventory->contact }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <select class="colors form-control" name="colors[]" multiple>
                                                            @foreach (Products::getCommonColors() as $singleColor)
                                                                <option value="{{ $singleColor }}">
                                                                    {{ $singleColor }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <p>Upload Feature Image:
                                                            <input type="file" accept="image/*" name="feature_img" required>
                                                        </p>
                                                        <div class="img-to-del d-inline-block position-relative"
                                                            style="max-width: 150px">
                                                            @isset($inventory->feature_img)
                                                                <img class="img-fluid"
                                                                    src="{{ asset($inventory->feature_img) }}">
                                                            @endisset
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-left">
                                                        <p>Upload Image Gallery:
                                                            <input type="file" accept="image/*" name="gallery[]"
                                                                multiple>
                                                        </p>
                                                        <div class="img-to-del-container">
                                                            @if ($inventory->images)
                                                                @foreach ($inventory->images as $img)
                                                                    <div class="img-to-del d-inline-block position-relative"
                                                                        style="max-width: 80px">
                                                                        <a href="/inventory/image/delete/{{ $img->id }}"
                                                                            class="text-sm position-absolute">
                                                                            <i class="fas fa-trash"></i>
                                                                        </a>
                                                                        <img class="img-fluid"
                                                                            src="{{ asset($img->product_image) }}">
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <p>
                                                            <input @if ($inventory->bike == 1) checked @endif
                                                                type="radio" name="vehicle" value="bike"
                                                                required> Cycle/Bike &emsp;
                                                            <input @if ($inventory->car == 1) checked @endif
                                                                type="radio" name="vehicle" value="car"
                                                                required> Car &emsp;
                                                            <input @if ($inventory->van == 1) checked @endif
                                                                type="radio" name="vehicle" value="van"
                                                                required> Van &emsp;
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <button
                                                            style="background: #ffcf42;color:black;font-weight: 600"
                                                            class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                            type="submit">
                                                            Add
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
                allowClear: false
            });
        });
    </script>
@endsection
