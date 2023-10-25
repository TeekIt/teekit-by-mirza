    <!-- jQuery -->
    <script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <!-- AdminLTE App -->
    <link rel="stylesheet" href="{{ asset('res/dist/css/jquery.timepicker.min.css') }}">
    <script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('res/dist/js/jquery.timepicker.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    @if (request()->getPathInfo() === '/login')
        @include('javascript.login-signup')
        @include('javascript.google-map')
    @endif

    @if (request()->getPathInfo() === '/settings/usergeneral')
        @include('javascript.seller-general-settings')
        @include('javascript.google-map')
    @endif

    @include('jquery.control-modals')
    
    <script>
        function checkbox() {
            $("#chkSelect").change(function() {
                if ($(this).is(":checked")) {
                    $("#content").show();
                } else {
                    $("#content").hide();
                }
            });
        }

        // *********************************************************************** //
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
