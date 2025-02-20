@extends('layouts.admin.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12">
                        <h4 class="py-4 my-1 text-site-primary">Dashboard</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box p-5 bg-info">
                            <div class="inner">
                                <h3>{{ $pendingOrders }}</h3>
                                <p>Pending Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                            {{-- <a href="{{ route('orders') }}" class="small-box-footer d-none">More info <i
                                    class="fas fa-arrow-circle-right"></i></a> --}}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box p-5 bg-success">
                            <div class="inner">
                                <h3>{{ $totalOrders }}</h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars fas fa-status-bar"></i>
                            </div>
                            {{-- <a href="{{ route('orders') }}" class="small-box-footer d-none">More info <i
                                    class="fas fa-arrow-circle-right"></i></a> --}}
                        </div>
                    </div>                   
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box p-5 bg-warning">
                            <div class="inner">
                                <h3>{{ $totalProducts }}</h3>
                                <p>Total Products</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-person-add"></i>
                            </div>
                            {{-- <a href="{{ route('inventory') }}" class="small-box-footer d-none">More info <i
                                    class="fas fa-arrow-circle-right"></i></a> --}}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box p-5 bg-danger">
                            <div class="inner">
                                <h3>£{{ $totalSales }}</h3>
                                <p>Total Sales</p>
                            </div>
                            {{-- <a href="{{ route('orders') }}" class="small-box-footer d-none">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div> --}}
                        </div>
                    </div>
                </div>
                
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
@endsection
