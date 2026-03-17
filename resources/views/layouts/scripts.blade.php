    <!-- jQuery -->
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- JQuery Multi Selector -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Sweet Alerts -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- Google Maps -->
    <script
        src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key={{ config('google.GOOGLE_PLACES_API_KEY') }}">
    </script>
    <script src="{{ asset('js/custom/CustomGoogleMapsClass.js') }}"></script>

    @php
        $googleMapRoutes = [route('seller.settings.general'), route('login')];
    @endphp
    @if (in_array(URL::current(), $googleMapRoutes))
        <script>
            new CustomGoogleMapsClass({
                mapCanvasId: 'map-canvas',
                mapAutoCompleteAddressId: 'modal_address',
                mapLatId: 'modal_lat',
                mapLongId: 'modal_long',
                mapCountryId: 'modal_country',
                mapStateId: 'modal_state',
                mapCityId: 'modal_city',
                mapPostcodeId: 'modal_postcode',
            }).initialize();

            const submitLocation = () => {
                document.getElementById("display_location").innerHTML = document.getElementById("modal_address").value;
                document.getElementById("address").value = document.getElementById("modal_address").value;
                document.getElementById("unit_address").value = document.getElementById("modal_unit_address").value;
                document.getElementById("postcode").value = document.getElementById("modal_postcode").value;
                document.getElementById("country").value = document.getElementById("modal_country").value;
                document.getElementById("state").value = document.getElementById("modal_state").value;
                document.getElementById("city").value = document.getElementById("modal_city").value;
                document.getElementById("address[lat]").value = document.getElementById("modal_lat").value;
                document.getElementById("address[lon]").value = document.getElementById("modal_long").value;

                $("#closeLocationModel").click();
            }
        </script>
    @endif

    @php
        $requestDeliveryRoutes = [route('seller.request.delivery.form')];
    @endphp
    @if (in_array(URL::current(), $requestDeliveryRoutes))
        <script>
            /* Initialize CustomGoogleMapsClass for pickup address autocomplete */
            const pickupGoogleMapsClass = new CustomGoogleMapsClass({
                mapAutoCompleteAddressId: 'pickupAddress',
            });

            const pickupAutoComplete = pickupGoogleMapsClass.handleAutoComplete();

            google.maps.event.addListener(pickupAutoComplete, 'place_changed', () => {
                const place = pickupAutoComplete.getPlace();
                if (place.geometry) {
                    /* Get the full formatted address from Google Places */
                    const fullAddress = `${place.name}, ${place.formatted_address}`;

                    pickupGoogleMapsClass.setAddress(fullAddress);
                    /* Update Livewire component properties */
                    if (window.Livewire) {
                        Livewire.find(document.querySelector('[wire\\:id]')
                                .getAttribute('wire:id'))
                            .call('updateLivewireProperties', null, null, null, null, fullAddress);
                    }
                }
            });

            /* Initialize CustomGoogleMapsClass for dropoff address autocomplete */
            const dropoffGoogleMapsClass = new CustomGoogleMapsClass({
                mapAutoCompleteAddressId: 'dropoffAddress',
                mapUnitAddressId: 'dropoffUnitAddress',
                mapLatId: 'dropoffLat',
                mapLongId: 'dropoffLon',
            });

            const dropoffAutoComplete = dropoffGoogleMapsClass.handleAutoComplete();

            google.maps.event.addListener(dropoffAutoComplete, 'place_changed', () => {
                const place = dropoffAutoComplete.getPlace();
                if (place.geometry) {
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    /* Get the full formatted address from Google Places */
                    const fullAddress = `${place.name}, ${place.formatted_address}`;
                    
                    dropoffGoogleMapsClass.setAddress(fullAddress);
                    dropoffGoogleMapsClass.setLatLong(lat, lng);
                    /* Update Livewire component properties */
                    if (window.Livewire) {
                        Livewire.find(document.querySelector('[wire\\:id]')
                                .getAttribute('wire:id'))
                            .call('updateLivewireProperties', lat, lng, fullAddress);
                    }
                }
            });
        </script>
    @endif

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

    <script>
        class DesktopNotifications {

            constructor(title, options) {
                this.title = title;
                this.options = options;
            }

            async checkNotificationPermission() {
                if (!("Notification" in window)) {
                    console.log("This browser does not support desktop notification");
                    return false;
                }

                if (Notification.permission === "granted") {
                    return true;
                } else if (Notification.permission !== "denied") {
                    const permission = await Notification.requestPermission();
                    return permission === "granted";
                }

                console.log("Please allow notifications for TeeIt 🥺");
                return false;
            }

            async sendNotification() {
                const hasPermission = await this.checkNotificationPermission();

                if (hasPermission) {
                    const notification = new Notification(this.title, this.options);
                    notification.addEventListener('click', () => {
                        window.open('https://app.teekit.co.uk/seller/orders', '_blank');
                    });
                }
            }

        }

        class SellerOrders {

            currentOrdersData = {};
            /* Set milliseconds */
            callContinueCountingAfter = 5000;

            startCounting() {
                $.ajax({
                    url: "{{ route('seller.orders.count') }}",
                    method: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: (data) => {
                        this.currentOrdersData = data;
                        this.continueCounting();
                    },
                    error: (jqXHR, textStatus, errorThrown) => {
                        console.error("logError: " + textStatus + " : " + errorThrown);
                    }
                });
            }

            continueCounting() {
                $.ajax({
                    url: "{{ route('seller.orders.count') }}",
                    method: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: (newOrdersData) => {

                        if (newOrdersData.total_orders > this.currentOrdersData?.total_orders) {
                            const desktopNotifications = new DesktopNotifications("New Order", {
                                body: "Please! Check Quickly You have received a new order.",
                                icon: "https://app.teekit.co.uk/teekit.png"
                            });
                            desktopNotifications.sendNotification();

                            document.getElementById('newOrderNotification1').play();
                            /**
                             * This timeout method is used to play 'newOrderNotification2' music
                             * just after 1sec of the arrival of a new order so that the user can
                             * clearly listen 'newOrderNotification1' sound
                             */
                            if (JSON.parse(newOrdersData.user_settings[0].settings).notification_music == 1)
                                document.getElementById('newOrderNotification2').play()

                            Swal.fire(
                                'New Order Alert!!',
                                'Please prepare the order soon',
                                'success'
                            )
                        }

                        this.currentOrdersData = newOrdersData;
                        setTimeout(() => this.continueCounting(), this.callContinueCountingAfter);
                    },
                    error: (jqXHR, textStatus, errorThrown) => {
                        console.error("logError: " + textStatus + " : " + errorThrown);
                    }
                });
            }

        }

        const sellerOrders = new SellerOrders();
        sellerOrders.startCounting();

        /*
         * General jQuery
         */
        $(window).mouseover(function() {
            document.getElementById('newOrderNotification2').pause();
        });

        gpt_box = jQuery('.change-height');

        max = jQuery(gpt_box[0]).height();

        jQuery.each(gpt_box, function(index, value) {
            if (jQuery(value).height() > max) {
                max = jQuery(value).height();
            }
        });

        jQuery.each(gpt_box, function(index, value) {
            jQuery(value).height(max);
        });

        $('.row.mb-2 h1.m-0.text-dark.text-center')
            .text($('.row.mb-2 h1.m-0.text-dark.text-center')
                .text()
                .replace('Admin Dashboard', ''));
        /*
         * JavaScript Event Listeners
         */
        document.addEventListener("DOMContentLoaded", () => {
            /*
             * Listening to Livewire events in JavaScript
             */
            Livewire.hook('component.initialized', (component) => {
                $('#businessHoursModal').modal('show')
            })
        });


    document.addEventListener('livewire:init', () => {
       Livewire.on('close-modal', (event) => {
           $('#' + event[0].id).modal('hide');
        });

        Livewire.on('show-modal', (event) => {
           $('#' + event[0].id).modal('show');
        });
    });
    
        /*
         * General JavaScript Methods
         */
        const closed = (day) => {
            let listOfClasses = document.getElementById("time[" + day + "][open]").className;
            console.log(listOfClasses.search("disabled-input-field"));
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

        const selectAll = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = !checkboxes[i].checked;
            }
        }

        const delUsers = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const users = [];
            let x = 0;
            for (let i = 0; i < checkboxes.length; i++) {
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

        const delOrders = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const orders = [];
            let x = 0;
            for (let i = 0; i < checkboxes.length; i++) {
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

        const delPromoCodes = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const promocodes = [];
            let x = 0;
            for (let i = 0; i < checkboxes.length; i++) {
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

        const delCategories = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const categories = [];
            let x = 0;
            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    categories[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (categories.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected categories?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.categories.del') }}",
                            type: "get",
                            data: {
                                "categories": categories
                            },
                            success: function(response) {
                                if (response == "Categories Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        const delVans = () => {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const vans = [];
            let x = 0;
            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    vans[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (vans.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected vans?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.vans.del') }}",
                            type: "get",
                            data: {
                                "vans": vans
                            },
                            success: function(response) {
                                if (response == "Vans Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        // const delVanInventories = () => {
        //     const checkboxes = document.querySelectorAll('.select-checkbox');
        //     const vanInventories = [];
        //     let x = 0;
        //     for (let i = 0; i < checkboxes.length; i++) {
        //         if (checkboxes[i].checked) {
        //             vanInventories[x] = checkboxes[i].id;
        //             x++;
        //         }
        //     }
        //     if (vanInventories.length != 0) {
        //         Swal.fire({
        //             title: 'Warning!',
        //             text: 'Are you sure you want to delete the selected van inventories?',
        //             icon: 'warning',
        //             showCancelButton: true,
        //             confirmButtonText: 'Yes'
        //         }).then((result) => {
        //             if (result.isConfirmed) {
        //                 $.ajax({
        //                     url: "",
        //                     type: "get",
        //                     data: {
        //                         "vans": vans
        //                     },
        //                     success: function(response) {
        //                         if (response == "Vans Deleted Successfully") {
        //                             window.location.reload();
        //                         }
        //                     }
        //                 });
        //             }
        //         });
        //     }
        // }
    </script>

    <script>
        $(document).ready(function() {
            $(".updateQty").on('submit', (function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {}
                });
            }));
        });

        function updateBulk() {
            $('#update_bulk').submit();
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var products = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    products[x] = checkboxes[i].id;
                    x++;
                }
            }
        }

        function changeHeight() {
            gpt_box = jQuery('.change-height');
            jQuery('.change-height').height('auto');
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
            setTimeout(changeHeight, 600);
        }
        changeHeight();
    </script>
