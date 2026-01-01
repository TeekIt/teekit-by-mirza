@php
    use App\Models\Products;
@endphp

@extends('layouts.shopkeeper.app')

@section('styles')
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3a4b83;
        }

        .card-body {
            padding: 30px 50px !important;
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
                                            <h4 class="text-center text-site-primary">Add Product</h4>
                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{ route('seller.add.single.inventory') }}" method="POST"
                                                enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="product_name"
                                                                placeholder="Title*" value="{{ old('product_name') }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="sku"
                                                                placeholder="SKU*" value="{{ old('sku') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control" name="category_id" required>
                                                                <option value="">Category*</option>
                                                                @foreach ($categories as $singleIndex)
                                                                    <option value="{{ $singleIndex->id }}"
                                                                        {{ old('category_id') == $singleIndex->id ? 'selected' : '' }}>
                                                                        {{ $singleIndex->category_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" name="qty"
                                                                placeholder="Stock*" value="{{ old('qty') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="price" placeholder="Price*"
                                                                value="{{ old('price') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="0.01" class="form-control"
                                                                name="discount_percentage" placeholder="Discount %"
                                                                value="{{ old('discount_percentage') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="height" placeholder="Height (cm)"
                                                                value="{{ old('height') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="width" placeholder="Width (cm)"
                                                                value="{{ old('width') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="length" placeholder="Length (cm)"
                                                                value="{{ old('length') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="number" step="any" class="form-control"
                                                                name="weight" placeholder="Weight (Kg)*"
                                                                value="{{ old('weight') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="brand"
                                                                placeholder="Brand" value="{{ old('brand') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control" name="status" required>
                                                                <option value=""
                                                                    @if (old('status') === null) selected @endif>
                                                                    Status*
                                                                </option>
                                                                <option @if (old('status') == 1) selected @endif
                                                                    value="1">
                                                                    Enabled
                                                                </option>
                                                                <option @if (old('status') == 0) selected @endif
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
                                                                    placeholder="Contact*" id="contact" name="contact"
                                                                    value="{{ old('contact') }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-left">
                                                        <select class="colors form-control" name="colors[]" multiple>
                                                            @foreach (Products::getCommonColors() as $singleColor)
                                                                <option value="{{ $singleColor }}"
                                                                    {{ in_array($singleColor, old('colors', [])) ? 'selected' : '' }}>
                                                                    {{ $singleColor }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <div class="form-group mt-3">
                                                            <label class="text-left d-block">Upload Feature Image*</label>
                                                            <div>
                                                                <input type="file" accept="image/*" name="feature_img" value="{{ old('feature_img') }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 text-left">
                                                        <div class="row mt-3">
                                                            <label class="text-left d-block">
                                                                Upload Image Gallery
                                                            </label>
                                                            <div>
                                                                <input type="file" accept="image/*"
                                                                    name="gallery[]" multiple>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <p>
                                                            <input @if (old('bike') == 1) checked @endif
                                                                type="radio" name="vehicle" value="bike" required>
                                                            Cycle/Bike &emsp;
                                                            <input @if (old('car') == 1) checked @endif
                                                                type="radio" name="vehicle" value="car" required>
                                                            Car &emsp;
                                                            <input @if (old('van') == 1) checked @endif
                                                                type="radio" name="vehicle" value="van" required>
                                                            Van &emsp;
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <button style="background: #ffcf42;color:black;font-weight: 600"
                                                            class="px-5 py-2 border-0 btn btn-secondary rounded-pill"
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
