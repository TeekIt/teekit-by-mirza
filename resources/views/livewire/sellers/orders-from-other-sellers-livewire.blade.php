<div class="container-xxl flex-grow-1 container-p-y">
    <div class="container pt-4">
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                {{ session()->get('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
    <!-- Main Content -->
    <div class="container">
        <div class="col-12">
            <h4 class="py-4 my-1">Orders From Other Sellers 2</h4>
        </div>
        <!-- Single Order Content -->
        <div class="col-12 p-2">
            <div class="card">
                <div class="card-body py-1 px-2">
                    <!-- Order Header -->
                    <div class="p-2 mb-2">
                        <table class="table table-striped table-responsive-sm">
                            <thead>
                                <tr class="col-12">
                                    <td colspan="6 col-12">
                                        <div class="row">
                                            <div class="col-12 col-md-10">
                                                <button class="btn btn-warning col-3 col-md-2" title="Hold the order">
                                                    <span wire:target="" wire:loading.remove>
                                                        Hold
                                                    </span>
                                                    {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                </button>

                                                <button class="btn btn-success col-4 col-md-2" title="Accept the order">
                                                    <span wire:target="" wire:loading.remove>
                                                        Accept
                                                    </span>    
                                                    {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                </button>

                                                <button class="btn btn-danger col-3 col-md-2" title="Reject the order">
                                                    <span wire:target="" wire:loading.remove>
                                                        Reject
                                                    </span>
                                                    {{-- <span>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span> --}}
                                                </button>
                                            </div>
                                            <div class="col-12 col-md-2 mt-md-1 mt-4">
                                                <p class="fs-3 fw-bold">5:00</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>Order#</b></td>
                                    <td>1669</td>
                                    <td><b>Order Status</b></td>
                                    <td><span class="badge badge-warning">pending</span></td>
                                </tr>

                                <tr>
                                    <td><b>Placed At</b></td>
                                    <td>2024-01-09 13:58:49</td>
                                    <td><b>Order Type</b></td>
                                    <td><span class="badge badge-info">delivery</span></td>
                                </tr>

                                <tr>
                                    <td><b>Order Total</b></td>
                                    <td>£60.732</td>
                                    <td><b>Payment Status</b></td>
                                    <td><span class="badge badge-primary">paid</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /Order Header -->
                    <div class="card-text">

                        <!-- Order Items -->
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <span class="img-container">

                                    {{-- <img class="d-block m-auto" src="{{ asset($item->feature_img) }}">
                                          
                                                <img class="d-block m-auto" src="{{ config('constants.BUCKET') . $item->feature_img }}"> --}}

                                </span>
                            </div>
                            <div class="col-12 col-sm-10">
                                <table class="table">
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Product Id: Remove in production</b></td>
                                        <td class="col-8">26075 </td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Product Name:</b></td>
                                        <td class="col-8">Stanley Window Scraper</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Category:</b></td>
                                        <td class="col-8">Hand Tools</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>SKU:</b></td>
                                        <td class="col-8">THSTY740</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>QTY:</b></td>
                                        <td class="col-8"> 3</td>
                                    </tr>
                                    <tr>
                                        <td class="col-4 text-site-primary"><b>Price:</b></td>
                                        <td class="col-8">£3.04 </td>
                                    </tr>

                                    {{-- <tr>
                                                    <td class="col-4 text-site-primary">
                                                        <b>I don't have this product!</b>
                                                        <button type="button" class="btn btn-site-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="If you don't have this product then go for the option selected by the user by clicking the front button.">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                        </button>
                                                    </td>
                                                    <td class="col-8">
                                                       
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal">
                                                                <i class="fas fa-search"></i>
                                                                Search Alternative
                                                            </button>
                                                      
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#removeItemFromOrderModel" >
                                                                <i class="fas fa-minus-circle"></i>
                                                                Remove Product
                                                            </button>
                                                       
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#sendToOtherStoresModal" >
                                                                <i class="fas fa-paper-plane"></i>
                                                                Send To Other Stores
                                                            </button>
                                                      
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#showCustomerContactModel" >
                                                                <i class="fas fa-phone-alt"></i>
                                                                Call The Customer
                                                            </button>
                                                       
                                                            <button type="button" class="btn btn-site-primary" data-bs-toggle="modal" data-bs-target="#searchAlternativeProductModal" >
                                                                <i class="fas fa-times"></i>
                                                                Cancel Order
                                                            </button>
                                                      
                                                    </td>
                                                </tr> --}}

                                </table>
                            </div>
                        </div>
                        <!-- /Order Items -->

                    </div>

                </div>
            </div>
        </div>
        <!-- /Single Order Content -->


        {{-- @if (!empty($data))
            <div class="row">
                <div class="col-md-12">
                    {{ $data->links() }}
                </div>
            </div>
        @endif --}}

    </div>
</div>