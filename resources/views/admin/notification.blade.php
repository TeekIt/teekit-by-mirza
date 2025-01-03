@extends('layouts.admin.app')
@section('content')

    <x-session-messages />

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h4 class="py-4 my-1">Send Notifications</h4>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 pb-4">
                    <div class="card-body">
                        <div class="d-block text-right">
                            <div class="card-text">
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('admin.notification.send') }}" method="POST"
                                            enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="title"
                                                            placeholder="Title*">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <textarea class="form-control" name="body" cols="30" rows="10"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 offset-md-3 text-center">
                                                    <button style="background: #ffcf42;color:black;font-weight: 600"
                                                        class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                        type="submit">Send</button>
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
@endsection
