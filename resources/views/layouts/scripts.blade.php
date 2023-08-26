    <!-- jQuery -->
    <script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>

    <!-- AdminLTE App -->
    <link rel="stylesheet" href="{{ asset('res/dist/css/jquery.timepicker.min.css') }}">
    <script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('res/dist/js/jquery.timepicker.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script !src="">
        $('.stimepicker').timepicker({
            timeFormat: 'h:mm p',
            interval: 30,
            startTime: '10:00',
            dynamic: true,
            dropdown: true,
            scrollbar: true
        });
        $('.etimepicker').timepicker({
            timeFormat: 'h:mm p',
            interval: 30,
            startTime: '10:00',
            dynamic: true,
            dropdown: true,
            scrollbar: true
        });
    </script>
    
    <script
        src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyDS4Nf8Ict_2h4lih9DCIt_EpkkBnVd85A">
    </script>
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

        function closed(day) {
            let listOfClasses = document.getElementById("time[" + day + "][open]").className;
            if (listOfClasses.search("disabled-input-field") < 0) {
                // To disable the input fields
                document.getElementById("time[" + day + "][open]").value = null;
                document.getElementById("time[" + day + "][close]").value = null;
                // To disable the input fields
                document.getElementById("time[" + day + "][open]").classList.add('disabled-input-field');
                document.getElementById("time[" + day + "][close]").classList.add('disabled-input-field');
                // To remove the required attribute from the input fields
                document.getElementById("time[" + day + "][open]").required = false;
                document.getElementById("time[" + day + "][close]").required = false;
            } else {
                // To enable the input fields
                document.getElementById("time[" + day + "][open]").classList.remove('disabled-input-field');
                document.getElementById("time[" + day + "][close]").classList.remove('disabled-input-field');
                // To add the required attribute from the input fields
                document.getElementById("time[" + day + "][open]").required = true;
                document.getElementById("time[" + day + "][close]").required = true;
            }
        }

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

        function signUp() {
            var spinner =
                '<div  class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
            let name = $('#name').val();
            let email = $('#email').val();
            let password = $('#password').val();
            let phone = $('#phone').val();
            let company_name = $('#company_name').val();
            let company_phone = $('#company_phone').val();
            let location_text = $('#location_text').val();
            let Address = [];
            let lat = $('input[id="Address[lat]"]').val();
            let lon = $('input[id="Address[lon]"]').val();
            let select_values = $('#select_values').val();
            let checked_value = 0;
            if ($('#chkSelect').is(':checked')) {
                checked_value = 1;
            }
            $('#signup').html(spinner);
            $.ajax({
                url: "{{ route('register') }}",
                type: "post",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    email: email,
                    password: password,
                    phone: phone,
                    company_name: company_name,
                    company_phone: company_phone,
                    location_text: location_text,
                    lat: lat,
                    lon: lon,
                    select_values: select_values,
                    checked_value: checked_value
                },
                success: function(response) {
                    $('#signup').text('Sign up');
                    if (response == "User Created") {
                        Swal.fire({
                            title: 'Success!',
                            text: 'We have received your store details we will contact you soon to verify your store',
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        $('.error').html('');
                        if (response.errors.name) {
                            // console.log(response.errors.name[0]);
                            $('.name').html('');
                            $('.name').html(response.errors.name[0]);
                        }
                        if (response.errors.email) {
                            $('.email').html(response.errors.email[0]);
                        }
                        if (response.errors.password) {
                            $('.password').html(response.errors.password[0]);
                        }
                        if (response.errors.phone) {
                            $('.phone').html(response.errors.phone[0]);
                        }
                        if (response.errors.company_name) {
                            $('.company_name').html(response.errors.company_name[0]);
                        }
                        if (response.errors.company_phone) {
                            $('.company_phone').html(response.errors.company_phone[0]);
                        }
                        if (response.errors.location_text) {
                            $('.location').html(response.errors.location_text[0]);
                        }
                        if ($('#chkSelect').is(":checked")) {
                            if (response.errors.select_values) {
                                $('.select_values').html(response.errors.select_values[0]);
                            }
                        }

                    }
                }
            });
        }

        function checkbox() {
            $("#chkSelect").change(function() {
                if ($(this).is(":checked")) {
                    $("#content").show();
                } else {
                    $("#content").hide();
                }
            });
        }
        // ***********************************************************************

        gpt_box = jQuery('.change-height');
        // console.log(gpt_box);
        max = jQuery(gpt_box[0]).height();
        //console.log(max);
        jQuery.each(gpt_box, function(index, value) {
            if (jQuery(value).height() > max) {
                max = jQuery(value).height();
            }
        });
        jQuery.each(gpt_box, function(index, value) {
            jQuery(value).height(max);
        });
        $('.row.mb-2 h1.m-0.text-dark.text-center').text($('.row.mb-2 h1.m-0.text-dark.text-center').text().replace(
            'Admin Dashboard', ''));

        function selectAll() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        }

        function delUsers() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var users = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    users[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (users.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected users?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.del.users') }}",
                            type: "get",
                            data: {
                                "users": users
                            },
                            success: function(response) {
                                if (response == "Users Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        function delDrivers() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var drivers = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    drivers[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (drivers.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected drivers?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.del.drivers') }}",
                            type: "get",
                            data: {
                                "drivers": drivers
                            },
                            success: function(response) {
                                if (response == "Drivers Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        function delOrders() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var orders = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    orders[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (orders.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected orders?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.del.orders') }}",
                            type: "get",
                            data: {
                                "orders": orders
                            },
                            success: function(response) {
                                if (response == "Orders Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        function delPromoCodes() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var promocodes = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    promocodes[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (promocodes.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected promo codes?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.promocodes.del') }}",
                            type: "get",
                            data: {
                                "promocodes": promocodes
                            },
                            success: function(response) {
                                if (response == "Promocodes Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        function updateStoreInfo() {
            var form = document.forms.namedItem("user_form");
            var formdata = new FormData(form);
            $.ajax({
                url: "{{ route('admin.image.update') }}",
                type: "post",
                contentType: false,
                data: formdata,
                processData: false,
                success: function(response) {
                    if (response == "Data Saved") {
                        Swal.fire({
                                title: 'Success!',
                                text: 'Data has been updated successfully',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            })
                            .then(function() {
                                location.reload();
                            });
                    } else {
                        $('.error').html('');
                        if (response.errors.name) {
                            $('.name').html(response.errors.name[0]);
                        }
                        if (response.errors.business_name) {
                            $('.business_name').html(response.errors.business_name[0]);
                        }
                        if (response.errors.phone) {
                            $('.phone').html(response.errors.phone[0]);
                        }
                        if (response.errors.business_phone) {
                            $('.business_phone').html(response.errors.business_phone[0]);
                        }
                        if (response.errors.store_image) {
                            $('.store_image').html(response.errors.store_image[0]);
                        }
                    }
                }
            });
        }

        function disableAll(ev) {
            alert('Am runin bro');
            return;
            ev.preventDefault();
            var urlToRedirect = ev.currentTarget.getAttribute(
                'href'
            ); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you want to disable all the products of your store?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed)
                    $("#DisableAll").click();
            });
        }
    </script>
    {{-- @yield('scripts') --}}
