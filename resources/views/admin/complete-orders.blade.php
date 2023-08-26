@extends('layouts.admin.app')

@section('links')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Completed Orders</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                <table id="" class="table text-center table-hover table-responsive-sm border-bottom">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th>#</th>
                            <th scope="col">Order Id</th>
                            <th>Total Items</th>
                            <th>Delivery Boy Name</th>
                            <th>Delivery Type</th>
                            <th>Receiver Name</th>
                            <th>Receiver Phone No</th>
                            <th>Receiver Complete Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($orders as $key => $order)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->total_items }}</td>
                                <td>{{ $order->f_name . ' ' . $order->l_name }}</td>
                                <td>{{ $order->type }}</td>
                                <td>{{ $order->name . ' ' . $order->l_name }}</td>
                                <td>{{ $order->phone_number }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($order->house_no . ' ' . $order->address, 50, $end = '...') }}
                                </td>
                                <td><a href="#" class="btn btn-danger"> Delete </a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center" style="padding-top: 10px;">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#complete-orders-table').DataTable({
                "pageLength": 20
            });
        });
    </script>
@endsection
