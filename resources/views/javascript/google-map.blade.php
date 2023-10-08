<script src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyDS4Nf8Ict_2h4lih9DCIt_EpkkBnVd85A"></script>

<script>
    // Google Map Code - Begins
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

        // get places auto-complete when user type in modal_location_text
        var input = /** @type {HTMLInputElement} */
            (document.getElementById('modal_location_text'));

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
    // Google Map Code - Ends

    function submitLocation() {
            $("#locationModel").click();
            var user_address = document.getElementById("modal_location_text").value;
            var user_lat = document.getElementById("ad_lat").value;
            var user_lon = document.getElementById("ad_long").value;
            document.getElementById("user_location").innerHTML = user_address;
            document.getElementById("location_text").value = user_address;
            document.getElementById("Address[lat]").value = user_lat;
            document.getElementById("Address[lon]").value = user_lon;
            $('#map_modal').modal('hide');
        }
</script>
