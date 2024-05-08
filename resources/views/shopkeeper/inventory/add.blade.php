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

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Inventory</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="offset-xl-2 col-lg-12 col-xl-8  pb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-block text-right">
                                    <div class="card-text">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4 class="text-center text-primary">Add Product</h4>
                                            </div>
                                            <div class="col-md-12">
                                                <form action="{{ route('seller.add.single.inventory') }}" method="POST" enctype="multipart/form-data">
                                                    {{ csrf_field() }}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control"
                                                                    name="product_name" placeholder="Title*" required
                                                                    id="" value="{{ $inventory->product_name }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="sku"
                                                                    placeholder="SKU*" required id=""
                                                                    value="{{ $inventory->sku }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <select class="form-control" required name="category_id"
                                                                    id="">
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
                                                                    placeholder="Stock*" required id=""
                                                                    value="{{ $inventory->qty }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="0.01" class="form-control"
                                                                    name="price" placeholder="Price*" required
                                                                    id="" value="{{ $inventory->price }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="0.01" class="form-control"
                                                                    name="discount_percentage" placeholder="Discount %"
                                                                    id=""
                                                                    value="{{ $inventory->discount_percentage }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="any" required
                                                                    class="form-control" name="height"
                                                                    placeholder="Height (cm)*" id=""
                                                                    value="{{ $inventory->height }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="any" required
                                                                    class="form-control" name="width"
                                                                    placeholder="Width (cm)*" id=""
                                                                    value="{{ $inventory->width }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="any" required
                                                                    class="form-control" name="length"
                                                                    placeholder="Length (cm)*" id=""
                                                                    value="{{ $inventory->length }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="number" step="any" required
                                                                    class="form-control" name="weight"
                                                                    placeholder="Weight (Kg)*" id=""
                                                                    value="{{ $inventory->weight }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="brand"
                                                                    placeholder="Brand" id=""
                                                                    value="{{ $inventory->brand }}">
                                                            </div>
                                                        </div>
                                                        <!-- <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" name="size" placeholder="Size" id="" value="{{ $inventory->size }}">
                                                                </div>
                                                            </div> -->
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <select class="form-control" required name="status"
                                                                    id="">
                                                                    <option value="">Status*</option>
                                                                    <option
                                                                        @if ($inventory->status == 1) selected @endif
                                                                        value="1">Published</option>
                                                                    <option
                                                                        @if ($inventory->status == 0) selected @endif
                                                                        value="0">Un Published</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                &nbsp;
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">+44</span>
                                                                </div>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <input type="text" class="form-control col-md-9"
                                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                                                    maxlength="10" placeholder="Contact*" id="contact"
                                                                    name="contact" value="{{ $inventory->contact }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-left">
                                                            <select class="colors form-control" name="colors[]"
                                                                multiple="multiple">
                                                                <option value="blue">Blue</option>
                                                                <option value="green">Green</option>
                                                                <option value="red">Red</option>
                                                                <option value="yellow">Yellow</option>
                                                                <option value="white">White</option>
                                                                <option value="black">Black</option>
                                                                <option value="orange">Orange</option>
                                                                <option value="pink">Pink</option>
                                                                <option value="brown">Brown</option>
                                                                <option value="indigo">Indigo</option>
                                                                <option value="purple">Purple</option>
                                                                <option value="gray">Gray</option>
                                                                <option value="silver">Silver</option>
                                                            </select>
                                                            <div class="row">
                                                                <div class="col-md-12 text-left">
                                                                    <p>Upload Image Gallery: &emsp;
                                                                        <input type="file"required accept="image/*"
                                                                            name="gallery[]" multiple>
                                                                    </p>
                                                                    <div class="img-to-del-container">
                                                                        @if ($inventory->images)
                                                                            @foreach ($inventory->images as $img)
                                                                                <div class="img-to-del d-inline-block position-relative"
                                                                                    style="max-width: 80px">
                                                                                    <a href="/inventory/image/delete/{{ $img->id }}"
                                                                                        class="text-sm position-absolute"><i
                                                                                            class="fas fa-trash"></i></a>
                                                                                    <img class="img-fluid"
                                                                                        src="{{ asset($img->product_image) }}"
                                                                                        alt="">
                                                                                </div>
                                                                            @endforeach
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-left">
                                                            <p>Upload Feature Image: &emsp;
                                                                <input type="file"required accept="image/*"
                                                                    name="feature_img">
                                                            </p>

                                                            <div class="img-to-del d-inline-block position-relative"
                                                                style="max-width: 150px">
                                                                <img class="img-fluid"
                                                                    src="{{ asset($inventory->feature_img) }}"
                                                                    alt="">
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
                                                                type="submit">{{ __('Add') }}</button>
                                                        </div>

                                                    </div>
                                                </form>
                                            </div>
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
