<div class="p-2">
    @if (json_decode($seller->business_hours)->submitted == null)
        {{-- ************************************ Set Store Hours Modal ************************************ --}}
        <div class="modal fade" id="business_hours_modal" data-backdrop="static">
            <div class="modal-dialog modal-dialog-custom">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Set Store Hours</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <form action="{{ route('time_update') }}" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="row form-inline">
                                    <div class="col-md-2 col-2">
                                        <div class="form-group">
                                            <label>Day &emsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="form-group">
                                            <label>Opening Time &emsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="form-group">
                                            <label>Closing Time &emsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-2">
                                        <div class="form-group">
                                            <label>Closed &emsp;</label>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                @endphp
                                @for ($i = 0; $i < count($days); $i++)
                                    <!-- Day & Time Sect Begins -->
                                    <div class="row form-inline">
                                        <div class="col-md-2 col-3">
                                            <div class="form-group">
                                                <p class="day">{{ $days[$i] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-4">
                                            <div class="form-group">
                                                <input type="text" name="time[{{ $days[$i] }}][open]" id="time[{{ $days[$i] }}][open]" class="stimepicker form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-4">
                                            <div class="form-group">
                                                <input type="text" name="time[{{ $days[$i] }}][close]" id="time[{{ $days[$i] }}][close]" class="etimepicker form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-1">
                                            <div class="form-group">
                                                &emsp;
                                                <input type="checkbox" name="time[{{ $days[$i] }}][closed]" onclick="closed('<?php echo $days[$i]; ?>')">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Day & Time Sect Ends -->
                                @endfor
                                <div class="col-md-12 text-center">
                                    <button style="background: #ffcf42;color:black;font-weight: 600;" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">{{ __('Submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    @endif
    <div class="content">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="text-dark text-center fs-1">Dashboard</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $pending_orders }}</h3>
                                <p>Pending Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                            <a href="{{ route('seller.orders') }}" class="small-box-footer">
                                More info
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $total_orders }}</h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars fas fa-status-bar"></i>
                            </div>
                            <a href="{{ route('seller.orders') }}" class="small-box-footer">
                                More info
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $total_products }}</h3>
                                <p>Total Products</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-person-add"></i>
                            </div>
                            <a href="{{ route('seller.inventory') }}" class="small-box-footer">
                                More info
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>£{{ $total_sales }}</h3>
                                <p>Total Sales</p>
                            </div>
                            <a href="{{ route('seller.orders') }}" class="small-box-footer">
                                More info
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h3 class="card-title"><strong>All Orders</strong></h3>
                                    </div>
                                    <div class="col-md-7">
                                        @if (json_decode($seller->settings)->notification_music == 1)
                                            <label class="switch float-right">
                                                <input type="checkbox" checked onclick="window.location.href='{{ route('change_settings', ['setting_name' => 'notification_music', 'value' => 0]) }}'">
                                                <span class="slider round"></span>
                                            </label>
                                            <h3 class="card-title float-right pr-3">Turn Off New Order Music</h3>
                                        @else
                                            <label class="switch float-right">
                                                <input type="checkbox" onclick="window.location.href='{{ route('change_settings', ['setting_name' => 'notification_music', 'value' => 1]) }}'">
                                                <span class="slider round"></span>
                                            </label>
                                            <h3 class="card-title float-right pr-3">Turn On New Order Music</h3>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Order No</th>
                                                <th>Status</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($all_orders as $key => $order)
                                                <tr>
                                                    <td>{{ $all_orders->firstItem() + $key }}</td>
                                                    <td>{{ $order->id }}</td>
                                                    <td>
                                                        @if ($order->order_status == 'pending')
                                                            <span class="badge bg-danger">Pending</span>
                                                        @elseif($order->order_status == 'accepted')
                                                            <span class="badge bg-info">Accepted</span>
                                                        @elseif($order->order_status == 'ready')
                                                            <span class="badge bg-purple">Ready</span>
                                                        @elseif($order->order_status == 'onTheWay')
                                                            <span class="badge bg-primary">On the Way</span>
                                                        @else
                                                            <span class="badge bg-success">Delivered</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('seller.orders', ['request_order_id' => $order->id]) }}" class="btn btn-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        You don't have any orders yet...!
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center" style="padding-top: 10px;">
                                    {{ $all_orders->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- User Info Card -->
                    <div class="col-12 col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title fw-bold">Seller Info</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <td>{{ $seller->name }} {{ $seller->l_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Business Name</th>
                                                <td>{{ $seller->business_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $seller->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td>{{ $seller->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Company Phone</th>
                                                <td>{{ $seller->business_phone }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
