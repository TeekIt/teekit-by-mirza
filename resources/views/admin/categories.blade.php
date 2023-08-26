@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        @if (session()->has('message'))
            <div class="alert alert-success">
                <div class="rmv_msg"> {{ session()->get('message') }}</div>
            </div>
        @endif
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Categories</h1>
                    <div class="container">
                        <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal"
                            class=" d-block text-right float-right btn btn-primary">Add Category</a>
                    </div>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">

        <div class="container">

            <div class="row">
                @foreach ($categories as $user)
                    <div class="modal fade" id="exampleModal{{ $user->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog" role="document">

                            <div class="modal-content">
                                <form method="post" action="{{ route('update_cat', ['id' => $user->id]) }}"
                                    enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{{ $user->category_name }}</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Name</label>
                                                    <input type="text" name="category_name" class="form-control"
                                                        value="{{ $user->category_name }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Category Image</label>
                                                    <input type="file" name="category_image" class="form-control"
                                                        value="{{ $user->category_name }}">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="modal-footer hidden ">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 pl-4 pr-4 pb-4">
                        <div class="change-height card" style="height: 317px;">
                            <div class="card-body">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal{{ $user->id }}"
                                    class="d-block text-right float-right" title="Edit">
                                    <img class="img-size-16" src="/res/res/img/edit.png">
                                </a>

                                <a href="{{ route('delete_cat', ['id' => $user->id]) }}"
                                    class="d-block text-right float-right del-icon" title="Delete">
                                    <img class="img-size-16" src="/res/res/img/delete.png">
                                </a>

                                <div class="card-text">
                                    <div class="col-md-12">
                                        <span class="img-container">
                                            <img class="d-block m-auto"
                                                src="{{ config('constants.BUCKET') . $user->category_image }}">
                                        </span>
                                    </div>
                                </div>

                                <div class="">
                                    <h3 class="d-block text-center p-3 pb-0 m-0 text-site-primary text-lg">
                                        <a href="#" class="d-block text-site-primary">{{ $user->category_name }}</a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-md-12">
                    {{ $categories->links() }}
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">

            <div class="modal-content">
                <form method="post" action="{{ route('add_cat') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Category</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input type="text" name="category_name" class="form-control" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Category Image</label>
                                    <input type="file" name="category_image" class="form-control" value="">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer hidden ">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        body .img-container img {
            width: auto;
            height: auto;
            max-width: 100%;
            height: auto;
            height: 160px;
            object-fit: scale-down;
        }
    </style>
@endsection
