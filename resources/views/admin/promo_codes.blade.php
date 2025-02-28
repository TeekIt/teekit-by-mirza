@extends('layouts.admin.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12 col-sm-6 col-md-7 col-xl-9">
                        <h4 class="py-4 my-1 text-site-primary">Promo Codes</h4>
                    </div>
                    <div class="col-12 col-md-5 col-xl-3 d-flex gap-2">
                        <button type="button" class="btn btn-success my-3 py-3 w-100" onclick="selectAll()"
                            title="Select All">
                            <span class="text-white">All</span>
                        </button>
                        <button type="button" class="btn btn-danger my-3 py-3 w-100" onclick="delPromoCodes()"
                            title="Delete Selected">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button data-bs-toggle="modal" data-bs-target="#add_promocodeModal"
                            class="btn btn-site-primary my-3 py-3 w-100" title="Add New">
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
                    <!-- modal start -->
                    @foreach ($promo_codes as $promo_code)
                        <div class="modal fade" id="promo_codeModal{{ $promo_code->id }}" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="post"
                                        action="{{ route('admin.promocodes.update', ['id' => $promo_code->id]) }}"
                                        enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="modal-header">
                                            <h5 class="modal-title display-center" id="exampleModalLabel">Edit</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Promo code</label>
                                                        <input type="text" name="promo_code" class="form-control"
                                                            value="{{ $promo_code->promo_code }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Discount type</label>
                                                        <select name="discount_type" class="form-control" required>
                                                            <option value="0"
                                                                @if ($promo_code->discount_type == 0) selected @endif>Percentage
                                                            </option>
                                                            <option value="1"
                                                                @if ($promo_code->discount_type == 1) selected @endif>Fixed
                                                                amount
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Discount</label>
                                                        <input type="number" name="discount" class="form-control"
                                                            value="{{ $promo_code->discount }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Order</label>
                                                        <input type="number" name="order_number" class="form-control"
                                                            value="{{ $promo_code->order_number }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Usage limit</label>
                                                        <input type="number" name="usage_limit" class="form-control"
                                                            value="{{ $promo_code->usage_limit }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Expiry date</label>
                                                        <input type="date" name="expiry_dt" class="form-control"
                                                            value="{{ $promo_code->expiry_dt }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Min discount</label>
                                                        <input type="number" name="min_amnt_for_discount"
                                                            class="form-control"
                                                            value="{{ $promo_code->min_amnt_for_discount }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Max discount</label>
                                                        <input type="number" name="max_amnt_for_discount"
                                                            class="form-control"
                                                            value="{{ $promo_code->max_amnt_for_discount }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <select name="store_id" class="form-control">
                                                            <option disabled selected>
                                                                Select store
                                                            </option>
                                                            @foreach ($stores as $store)
                                                                <option
                                                                    {{ $promo_code->store_id == $store->id ? 'selected' : '' }}
                                                                    value="{{ $store->id }}">
                                                                    {{ $store->business_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer hidden">
                                            <button type="button" class="btn btn-secondary rounded-pill px-5 py-2"
                                                data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn site-primary-yellow-bg rounded-pill px-5 py-2">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <!-- modal end -->

                    <!-- Add form modal starts -->
                    <div class="modal fade" id="add_promocodeModal" tabindex="-1" role="dialog"
                        aria-labelledby="add_promocodeModalLabel" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <form action="{{ route('admin.promocodes.add') }}" method="POST">
                                            {{ csrf_field() }}
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Add Promo Code</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="promo_code"
                                                            id="promo_code" placeholder="Promo Code*" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select name="discount_type" class="form-control" required>
                                                            <option disabled selected>Select type
                                                            </option>
                                                            <option value="0">Percentage
                                                            </option>
                                                            <option value="1">Fixed amount
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="discount"
                                                            id="discount" placeholder="Discount*" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="order_number"
                                                            id="order_number" placeholder="Valid for order# (optional)">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="usage_limit"
                                                            id="usage_limit" placeholder="Usage limit (optional)">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="date" class="form-control" name="expiry_dt"
                                                            id="expiry_dt" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control"
                                                            name="min_amnt_for_discount" id="min_amnt_for_discount"
                                                            placeholder="Min amount for discount*" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control"
                                                            name="max_amnt_for_discount" id="max_amnt_for_discount"
                                                            placeholder="Max amount for discount*" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <select name="store_id" class="form-control">
                                                            <option disabled selected>
                                                                Select store
                                                            </option>
                                                            @foreach ($stores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{ $store->business_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer hidden ">
                                                    <button type="button"
                                                        class="btn btn-secondary rounded-pill px-5 py-2"
                                                        data-bs-dismiss="modal">
                                                        Close
                                                    </button>
                                                    <button class="btn site-primary-yellow-bg rounded-pill px-5 py-2"
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
                    <!-- Add form modal ends -->

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table text-center table-hover table-responsive-sm border-bottom">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th scope="col">#</th>
                                            <th></th>
                                            <th scope="col">Promo Code</th>
                                            <th scope="col">Discount</th>
                                            <th scope="col">Order#</th>
                                            <th scope="col">Expiry Date</th>
                                            <th scope="col">Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promo_codes as $promo_code)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <input type="checkbox" class="select-checkbox" title="Select"
                                                        id="{{ $promo_code->id }}">
                                                </td>
                                                <td>{{ $promo_code->promo_code }}</td>
                                                @if ($promo_code->discount_type == '0')
                                                    <td>{{ $promo_code->discount }}%</td>
                                                @else
                                                    <td>£{{ $promo_code->discount }}</td>
                                                @endif
                                                <td>{{ $promo_code->order_number }}</td>
                                                <td>{{ $promo_code->expiry_dt }}</td>
                                                <td>
                                                    <button data-bs-toggle="modal"
                                                        data-bs-target="#promo_codeModal{{ $promo_code->id }}"
                                                        class="btn text-site-primary">
                                                        <i class="far fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-center" style="padding-top: 10px;">
                                    {{ $promo_codes->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
@endsection
