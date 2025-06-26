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
                                        <h4 class="text-center text-site-primary">Request Delivery For Buyer</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <form wire:submit.prevent="requestDelivery" method="POST" enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" wire:model.defer="pickupAddress"
                                                            id="pickupAddress" placeholder="Pickup Address*"
                                                            value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" wire:model.defer="dropoffAddress"
                                                            placeholder="Dropoff Address*" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" wire:model.defer="receiverName"
                                                            placeholder="Buyer Name*" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" wire:model.defer="receiverPhone"
                                                            placeholder="Buyer Contact*" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.defer="packageSize" required>
                                                            <option value="">Package Size*</option>
                                                            <option vlaue="Moped">Moped</option>
                                                            <option vlaue="Car Boot">Car Boot</option>
                                                            <option vlaue="Small Van">Small Van</option>
                                                            <option vlaue="Big Van">Big Van</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.defer="packageWeight" required>
                                                            <option value="">Package Weight (Kg)*</option>
                                                            <option value="< 5Kg">
                                                                < 5Kg </option>
                                                            <option value="< 10Kg">
                                                                < 10Kg </option>
                                                            <option value="< 15Kg">
                                                                < 15Kg </option>
                                                            <option value="> 15Kg">
                                                                > 15Kg
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="p-3 text-center">
                                                        <p class="fs-5">Total Cost: Coming Soon{{-- £30 --}}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 offset-md-3 text-center">
                                                <button type="submit"
                                                    class="btn site-primary-yellow-bg rounded-pill px-5 py-2 font-weight-bold">
                                                    Request
                                                </button>
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

    <script
        src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key={{ config('google.GOOGLE_PLACES_API_KEY') }}">
    </script>

    <script>
        function handleAutoComplete() {
            /* Get places auto-complete when user types-in */
            var address = /** @type {HTMLInputElement} */ (document.getElementById('pickupAddress'));

            var autocomplete = new google.maps.places.Autocomplete(address, {
                componentRestrictions: {
                    country: ["uk", "pk"]
                },
                fields: ["address_components", "geometry"]
            });

            return autocomplete;
        }

        window.addEventListener('load', handleAutoComplete());
        /* Google Map Code - Ends */
    </script>

</div>
