<div class="p-2">
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
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalVans }}</h3>
                                <p>Total Vans</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shuttle-van"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $totalStock }}</h3>
                                <p>Total Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $activeOperatives }}</h3>
                                <p>Active Operatives</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $totalStockValue }}</h3>
                                <p>Total Stock Value</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-pound-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $lowStockAlerts }}</h3>
                                <p>Low Stock Alerts</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $pendingOrders }}</h3>
                                <p>Pending Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
