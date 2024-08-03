    <!-- jQuery -->
    <script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('res/dist/js/jquery.timepicker.min.js') }}"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    <!-- AdminLTE App -->
    <script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    @php
        $google_map_routes = ['login', 'seller/settings/general'];
    @endphp
    @if (in_array(Route::current()->uri, $google_map_routes))
        @include('javascript.google-map-js')
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

                console.log("Please allow notifications for TeeIt :(");
                return false;
            }

            async sendNotification() {
                const hasPermission = await this.checkNotificationPermission();

                if (hasPermission) {
                    const notification = new Notification(this.title, this.options);
                    notification.addEventListener('click', () => {
                        window.open('https://teekitstaging.shop/seller/orders', '_blank');
                    });
                }
            }

        }

        class SellerOrders {

            currentOrdersData = {};
            /* Set milliseconds */
            callContinueCountingAfter = 2000;

            startCounting() {
                $.ajax({
                    url: "{{ route('seller.orders.count') }}",
                    method: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (data) => {
                        this.currentOrdersData = data;
                        console.log(this.currentOrdersData);
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
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (newOrdersData) => {

                        if (newOrdersData.total_orders > this.currentOrdersData?.total_orders) {
                            const desktopNotifications = new DesktopNotifications("New Order", {
                                body: "Please! Check Quickly You have received a new order.",
                                icon: "https://app.teekit.co.uk/teekit.png"
                            });
                            desktopNotifications.sendNotification();

                            document.getElementById('newOrderNotification1').play();
                            Swal.fire(
                                'New Order Alert!!',
                                'Please prepare the order soon',
                                'success'
                            )
                            /**
                             * This timeout method is used to play 'newOrderNotification2' music
                             * just after 1sec of the arrival of a new order so that the user can
                             * clearly listen 'newOrderNotification1' sound
                             */
                            if (JSON.parse(newOrdersData.user_settings[0].settings).notification_music == 1)
                                document.getElementById('newOrderNotification2').play()
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

        window.addEventListener('close-modal', event => $('#' + event.detail.id).modal('hide'));

        window.addEventListener('show-modal', event => $('#' + event.detail.id).modal('show'));
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

        const checkbox = () => {
            $("#chkSelect").change(function() {
                if ($(this).is(":checked")) {
                    $("#content").show();
                } else {
                    $("#content").hide();
                }
            });
        }

        const selectAll = () => {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        }

        const delUsers = () => {
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

        const delDrivers = () => {
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

        const delOrders = () => {
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

        const delPromoCodes = () => {
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

        const updateStoreInfo = () => {
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
    </script>
