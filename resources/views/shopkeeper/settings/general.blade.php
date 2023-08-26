@extends('layouts.shopkeeper.app')
@section('content')
<div class="content">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">General Settings</h1>
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
                    <h4 class="text-left text-primary">Store Image</h4>
                    <div class="card">
                        <div class="card-body-custom">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{route('user_img_update')}}" method="POST"
                                                enctype="multipart/form-data">
                                                {{csrf_field()}}
                                                <div class="row form-inline">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label><img class="img img-fluid img-thumbnail"
                                                                    src="{{config('constants.BUCKET') . auth()->user()->user_img}}"
                                                                    alt="No Image Uploaded"></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label><input type="file" accept="image/*" name="user_img"
                                                                    required></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="">
                                                            <div class="text-center">
                                                                <button
                                                                    style="background: #ffcf42;color:black;font-weight: 600"
                                                                    class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                                    type="submit">Update</button>
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

        </div><!-- /.container-fluid -->

        <div class="container-fluid">
            <div class="row">
                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <h4 class="text-left text-primary">Location</h4>
                    <div class="card">
                        <div class="card-body-custom">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{route('payment_settings_update')}}" method="POST"
                                                enctype="multipart/form-data">
                                                {{csrf_field()}}

                                                <div class="row form-inline">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label data-bs-toggle="modal" data-target="#map_modal">Set
                                                                Location: &emsp; <i
                                                                    class="fa fa-map-marker text-danger"></i>
                                                                {{substr($address, 0, 15) . '...'}}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label data-bs-toggle="modal" data-bs-target="#map_modal">Use
                                                                Current Location: &emsp; <i
                                                                    class="fa fa-map-marked text-primary"></i></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="">
                                                            <div class="text-center">
                                                                <button
                                                                    style="background: #ffcf42;color:black;font-weight: 600"
                                                                    class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                                    onclick="document.getElementById('update_location').click();"
                                                                    type="button">Update</button>
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
        </div><!-- /.container-fluid -->

        <div class="container-fluid">
            <div class="row">
                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <h4 class="text-left text-primary">Update Password</h4>
                    <div class="card">
                        <div class="card-body-custom">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{route('password_update')}}" method="POST">
                                                {{csrf_field()}}
                                                <div class="row form-inline">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="password" class="form-control"
                                                                name="old_password" placeholder="Old Password" required
                                                                id="old_password" minlength="8">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="password" class="form-control"
                                                                name="new_password" placeholder="New Password" required
                                                                id="new_password" minlength="8">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="">
                                                            <div class="text-center">
                                                                <button
                                                                    style="background: #ffcf42;color:black;font-weight: 600"
                                                                    class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                                    type="submit">Update</button>
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
        </div><!-- /.container-fluid -->

        <!-- /.container-fluid -->
        <div class="container-fluid">
            <div class="row">
                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <h4 class="text-left text-primary">Export Products</h4>
                    <div class="card">
                        <div class="card-body-custom">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row form-inline">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        {{-- <label >Browse Data: &emsp;</label>--}}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <div class="text-center">
                                                            <a href="{{route('exportProducts')}}"
                                                                style="background: #3a4b83;color:white;font-weight: 600"
                                                                class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                                type="submit">Export</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->

        </div><!-- /.container-fluid -->

        <div class="container-fluid">
            <div class="row">
                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <h4 class="text-left text-primary">Update Store Hours</h4>
                    <div class="card">
                        <div class="card-body-custom">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{route('time_update')}}" method="POST"
                                                enctype="multipart/form-data">
                                                {{csrf_field()}}
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
                                                <?php
                                                $bh = json_decode($business_hours, true);
                                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                for ($i = 0; $i < count($days); $i++) {
                                                ?>
                                                <!-- Day & Time Sect Begin -->
                                                <div class="row form-inline">
                                                    <div class="col-md-2 col-3">
                                                        <div class="form-group">
                                                            <p class="day">{{$days[$i]}}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 col-4">
                                                        <div class="form-group">
                                                            <input type="text" name="time[{{$days[$i]}}][open]"
                                                                id="time[{{$days[$i]}}][open]"
                                                                value="<?php echo (isset($bh['time'][$days[$i]]['open'])) ? $bh['time'][$days[$i]]['open'] : '' ?>"
                                                                class="stimepicker form-control <?php echo (isset($bh['time'][$days[$i]]['closed'])) ? 'disabled-input-field' : '' ?>"
                                                                <?php echo (isset($bh['time'][$days[$i]]['closed'])) ? '' : 'required' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 col-4">
                                                        <div class="form-group">
                                                            <input type="text" name="time[{{$days[$i]}}][close]"
                                                                id="time[{{$days[$i]}}][close]"
                                                                value="<?php echo (isset($bh['time'][$days[$i]]['close'])) ? $bh['time'][$days[$i]]['close'] : '' ?>"
                                                                class="etimepicker form-control <?php echo (isset($bh['time'][$days[$i]]['closed'])) ? 'disabled-input-field' : '' ?>"
                                                                <?php echo (isset($bh['time'][$days[$i]]['closed'])) ? '' : 'required' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 col-1">
                                                        <div class="form-group">
                                                            &emsp;
                                                            <input type="checkbox" name="time[{{$days[$i]}}][closed]"
                                                                onclick="closed('<?php echo $days[$i] ?>')"
                                                                <?php echo (isset($bh['time'][$days[$i]]['closed'])) ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Day & Time Sect End -->
                                                <?php
                                                }
                                                ?>

                                                <div class="col-md-12 text-center">
                                                    <button style="background: #ffcf42;color:black;font-weight: 600"
                                                        class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                        type="submit">{{__('Update')}}</button>
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
        </div><!-- /.container-fluid -->

    </div>
    <!-- /.content -->
</div>
<!-- Google Map Modal - Begins -->
<div class="modal fade" id="map_modal">
    <div class="modal-dialog modal-lg  modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add Location</h4>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <form action="{{route('location_update')}}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}

                <div class="modal-body">
                    <div class="row">
                        <div class="card">
                            <div class="card-body-custom">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12 mt-3 mb-3">

                                            <div class="form-group" style="height:100%; width:100%">
                                                <input type="text" class="form-control" value="<?php echo $address; ?>"
                                                    name="location_text" id="location_text" />
                                                <div class="mt-3 mb-3"
                                                    style="height: 100%; width: 100%; margin: 0px; padding: 0px;    min-height: 200px;"
                                                    id="map-canvas"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                            <br>
                                            <br>
                                        </div>
                                        <?php
                                        if ($business_location) {
                                            $bh = json_decode($business_location, true);
                                            if (empty($bh['lat'])) {
                                                $bh['lat'] = "";
                                            }
                                            if (empty($bh['long'])) {
                                                $bh['long'] = "";
                                            }
                                        } else {
                                            $bh['lat'] = "";
                                            $bh['long'] = "";
                                        }
                                        ?>
                                        <div class="col-md-6">
                                            <label for="Address">Lat
                                            </label>
                                            <input required type="text" id="ad_lat" name="Address[lat]"
                                                class="form-control" value="<?php echo $bh['lat'] ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Address">Long
                                            </label>
                                            <input required type="text" id="ad_long" name="Address[long]"
                                                class="form-control" value="<?php echo $bh['long']; ?>">
                                        </div>
                                        <div class="col-md-12"></div>
                                        <button id="update_location" type="submit"
                                            class="d-no mt-3 btn btn-submit btn-block btn-outline-primary">Submit</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
<!-- Google Map Modal - Ends -->

<!-- Google Map Code - Begins -->
<script
    src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyDS4Nf8Ict_2h4lih9DCIt_EpkkBnVd85A">
</script>
<script>
var map;
var marker;

function initialize() {

    var mapOptions = {
        zoom: 12
    };
    map = new google.maps.Map(document.getElementById('map-canvas'),
        mapOptions);

    // Get GEOLOCATION
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = new google.maps.LatLng(position.coords.latitude,
                position.coords.longitude);

            map.setCenter(pos);
            // marker = new google.maps.Marker({
            //     position: pos,
            //     map: map,
            //     draggable: true
            // });
        }, function() {
            handleNoGeolocation(true);
        });
    } else {
        // Browser doesn't support Geolocation
        handleNoGeolocation(false);
    }

    function handleNoGeolocation(errorFlag) {
        if (errorFlag) {
            var content = 'Error: The Geolocation service failed.';
        } else {
            var content = 'Error: Your browser doesn\'t support geolocation.';
        }

        var options = {
            map: map,
            position: new google.maps.LatLng(60, 105),
            content: content
        };

        map.setCenter(options.position);
        marker = new google.maps.Marker({
            position: options.position,
            map: map,
            draggable: true
        });


    }

    // get places auto-complete when user type in location_text
    var input = /** @type {HTMLInputElement} */
        (document.getElementById('location_text'));


    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);

    var infowindow = new google.maps.InfoWindow();
    marker = new google.maps.Marker({
        map: map,
        anchorPoint: new google.maps.Point(0, -29),
        draggable: true
    });
    google.maps.event.addListener(marker, "dragend", function() {
        var lat, long;

        console.log('i am dragged');
        lat = marker.getPosition().lat();
        long = marker.getPosition().lng();

        set_lat_lng(lat, long);

    });

    function set_lat_lng(lat, lng) {
        document.getElementById("ad_lat").value = lat;
        document.getElementById("ad_long").value = lng;
    }

    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        infowindow.close();
        marker.setVisible(true);
        console.log();
        lat = autocomplete.getPlace().geometry.location.lat();
        long = autocomplete.getPlace().geometry.location.lng();
        var place = autocomplete.getPlace();
        set_lat_lng(lat, long);
        if (!place.geometry) {
            return;
        }

        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17); // Why 17? Because it looks good.
        }
        marker.setIcon( /** @type {google.maps.Icon} */ ({
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(35, 35)
        }));
        marker.setPosition(place.geometry.location);
        marker.setVisible(true);
        var address = '';
        if (place.address_components) {
            address = [
                (place.address_components[0] && place.address_components[0].short_name || ''), (place
                    .address_components[1] && place.address_components[1].short_name || ''), (place
                    .address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
        }

    });
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>
<!-- Google Map Code - Ends -->
@endsection