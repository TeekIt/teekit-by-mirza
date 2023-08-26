@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                    <a href="/queries" class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Queries</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12 pl-4 pr-4 pb-4">
                    <div class="card">
                        <div class="card-body p-5">
                            <table class="table ">

                                <tr >
                                    <td>example@123.cp</td>
                                    <td>Login System Help</td>
                                    <td>March 10, 2001, 5:16 pm</td>
                                    <td><a href="" class=" d-block text-right text-site-primary"><span style=" font-size: 25px; vertical-align: -5px; margin-right: 20px; ">Edit</span> <img class="img-size-16" src="res/img/edit.png" alt=""></a></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection
