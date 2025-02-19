<div class="content">

    <x-session-messages />

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="offset-xl-2 col-lg-12 col-xl-8 py-4">
                    <div class="card-body">
                        <div class="d-block text-right">
                            <div class="card-text">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="text-center text-primary">Request Delivery For Buyer</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <form action="#" method="POST"
                                            enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control"
                                                            name="product_name" placeholder="Origin*"
                                                            value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="destination"
                                                            placeholder="Destination*" value=""
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="destination"
                                                            placeholder="Buyer Name*" value=""
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="destination"
                                                            placeholder="Buyer Contact*" value=""
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="destination"
                                                            placeholder="Product Weight (Kg)*" value=""
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <p>Total Cost: £30</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 offset-md-3 text-center">
                                                    <button
                                                        style="background: #ffcf42;color:black;font-weight: 600"
                                                        class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                        type="submit">
                                                        Request
                                                    </button>
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
    <style>
        .card-body {
            padding: 30px 50px !important;
        }
    </style>
</div>
