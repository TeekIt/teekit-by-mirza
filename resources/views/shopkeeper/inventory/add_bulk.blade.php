@extends('layouts.shopkeeper.app')
@section('content')
<div class="content">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Import Bulk Products From .csv/.xlsx File</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <h4 class="text-left text-primary">Import Products</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{route('importProducts')}}" method="post" enctype="multipart/form-data">
                                                {{csrf_field()}}
                                                <div class="row form-inline">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="">Browse Data: &emsp;</label>
                                                            <input required name="file" accept="application/csvm+json" type="file">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="">
                                                            <div class="text-center">
                                                                <button style="background: #ffcf42;color:black;font-weight: 600" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">Import</button>
                                                            </div>
                                                        </div>
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
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<style>
    .card-body {
        padding: 30px 50px !important;
    }
</style>
@endsection