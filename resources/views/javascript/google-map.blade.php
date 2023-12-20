<script src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyDS4Nf8Ict_2h4lih9DCIt_EpkkBnVd85A"></script>

<script>
    /* 
     * For help related to this API please visit: 
     * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform#maps_places_autocomplete_addressform-javascript 
     */

    // Google Map Code - Begins
    var map;
    var marker;

    function initialize() {

        var mapOptions = {
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

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
        var address = /** @type {HTMLInputElement} */
            (document.getElementById('modal_location_text'));

        // var autocomplete = new google.maps.places.Autocomplete(address);
        var autocomplete = new google.maps.places.Autocomplete(address, {
            componentRestrictions: {
                country: ["uk", "pk"]
            },
            fields: ["address_components", "geometry"]
        });
        autocomplete.bindTo('bounds', map);

        var infowindow = new google.maps.InfoWindow();

        marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            draggable: true
        });

        google.maps.event.addListener(marker, "dragend", function() {
            var lat, long;
            lat = marker.getPosition().lat();
            long = marker.getPosition().lng();
            setLatLong(lat, long);
        });

        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            infowindow.close();
            marker.setVisible(true);

            lat = autocomplete.getPlace().geometry.location.lat();
            long = autocomplete.getPlace().geometry.location.lng();
            setLatLong(lat, long);

            var place = autocomplete.getPlace();
            // Get each component of the address from the place details,
            // and then fill-in the corresponding field on the form.
            // place.address_components are google.maps.GeocoderAddressComponent objects
            // which are documented at http://goo.gle/3l5i5Mr
            for (const component of place.address_components) {
                // @ts-ignore remove once typings fixed
                const componentType = component.types[0];
                switch (componentType) {
                    case "locality":
                        document.querySelector("#modal_city").value = component.long_name;
                        break;
                        // In the UK and Sweden, the component to display the city is postal_town
                    case "postal_town":
                        document.querySelector("#modal_city").value = component.long_name;
                        break;
                    case "administrative_area_level_1":
                        document.querySelector("#modal_state").value = component.short_name;
                        break;
                    case "country":
                        document.querySelector("#modal_country").value = component.long_name;
                        break;
                }
            }

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
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }
        });
    }

    const setLatLong = (lat, lng) => {
        document.getElementById("modal_lat").value = lat;
        document.getElementById("modal_long").value = lng;
    }

    // google.maps.event.addDomListener(window, 'load', initialize);
    window.addEventListener('load', initialize);
    // Google Map Code - Ends

    const submitLocation = () => {
        document.getElementById("form_location_text").innerHTML = document.getElementById("modal_location_text").value;
        document.getElementById("user_address").value = document.getElementById("modal_location_text").value;
        document.getElementById("user_country").value = document.getElementById("modal_country").value;
        document.getElementById("user_state").value = document.getElementById("modal_state").value;
        document.getElementById("user_city").value = document.getElementById("modal_city").value;
        document.getElementById("address[lat]").value = document.getElementById("modal_lat").value;
        document.getElementById("address[lon]").value = document.getElementById("modal_long").value;
        $("#locationModel").click();
        // $('#map_modal').modal('hide');
    }
</script>
