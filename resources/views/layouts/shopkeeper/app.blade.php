<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        @include('layouts.shopkeeper.navbar')
        <!-- /.navbar -->
        <!-- Main Sidebar Container -->
        @include('layouts.shopkeeper.sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12">
                    @include('flash::message')
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
            @yield('content')
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <audio id="new_order_notification1">
        <source src="{{ asset('audio/TeekItaa.mp4') }}" type="audio/mp4">
    </audio>
    <audio id="new_order_notification2" loop>
        <source src="{{ asset('audio/TeekItNotificationMusic (mp3cut.net).mp3') }}" type="audio/mp3">
    </audio>

    <!-- jQuery -->
    <script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('res/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>

    <!-- AdminLTE App -->
    <script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>

    <style>
        .modal-dialog-custom {
            max-width: 700px;
        }

        table tr:first-of-type td {
            border-top: 0;
        }

        .brand-link {
            background-color: white;
        }

        [class*=sidebar-dark-] {
            background-color: #3a4b83;
        }

        [class*=sidebar-dark-] * {
            color: #fff;
        }

        .text-primary {
            color: #3a4b83 !important;
        }

        nav.main-header {
            box-shadow: 0 0px 1px rgba(0, 0, 0, .25), 0 4px 15px rgba(0, 0, 0, .22) !important;
        }

        .brand-link .brand-image {
            max-height: 90px;
        }

        nav.main-header {
            min-height: 120px;
        }

        .navbar-light .navbar-nav .nav-link,
        nav.main-header a {
            color: #3a4b83;
            font-weight: 600;
        }

        .brand-link {
            min-height: 120px;
        }

        .brand-link .brand-image {
            float: unset;
            margin: 0 auto;
            display: block;
        }

        [class*=sidebar-dark] .brand-link {
            margin: 0;
            padding: 0;
        }

        .brand-link .brand-image {
            padding-top: 25px;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover {
            width: 4.6rem;
        }

        .checked {
            color: orange;
        }

        .nav-sidebar-arrow {
            position: absolute;
            top: 55%;
            right: -17px;
            cursor: pointer;
            background: #ffcf42;
            border-radius: 100% 100%;
            padding: 0;
        }

        .nav-sidebar-arrow img {
            max-width: inherit;
            max-height: 40px;
        }

        .sidebar-collapse .nav-sidebar-arrow img {
            transform: rotate(178deg);
        }

        .content-header h1 {
            font-size: 3.5rem;
        }

        .text-site-primary {
            color: #3a4b83;
        }

        .card-body {
            padding: 5px 5px;
        }

        .img-container {
            background: #f4f6f9;
            display: block;
            margin-left: 30px;
            margin-right: 30px;
            padding-top: 25px;
            padding-bottom: 25px;
            border-radius: 20px;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active {
            box-shadow: unset;
            color: #ffcf42;
            background-color: unset;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active *,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active * {
            color: #ffcf42;
        }

        .img-container {
            background: #f4f6f9;
            display: block;
            margin-left: 30px;
            margin-right: 30px;
            padding-top: 25px;
            padding-bottom: 25px;
            border-radius: 20px;
            width: 100%;
            height: auto;
            margin: 0;
            padding: 10px;
            border-radius: 7px;
        }

        .img-container img {
            width: auto;
            height: auto;
            max-width: 100%;
            height: auto;
        }

        .pt-30 {
            padding-top: 30px;
        }

        .pb-30 {
            padding-bottom: 30px;
        }

        .card-body {
            padding: 5px 15px;
        }

        .color-circle {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 100vw;
        }

        .color-red {
            background: red;
        }

        .ui-timepicker-standard {
            margin-top: -242px !important;
            z-index: 1100 !important;
        }

        input.form-control,
        select.form-control {
            border: 0;
            border-bottom: 1px solid;
            border-radius: 0;
            color: #8aa7d7;
            border-color: #4a7ed6;
            padding-left: 3px;
            background: transparent;
            background-color: transparent !important;
        }

        .form-control:focus {
            color: #495057;
            background-color: transparent;
            border-color: #80bdff;
            color: #8aa7d7 !important;
        }
    </style>

    {{-- <link rel="stylesheet" href="{{ asset('res/dist/css/jquery.timepicker.min.css') }}"> --}}
    {{-- <script src="{{ asset('res/dist/js/jquery.timepicker.min.js') }}"></script> --}}
    {{-- <script>
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
    </script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> --}}
    <script>
        var total_orders = 0;
        /**
         * This AJAX call will run automatically
         * on each page load & after its completion
         * it will trigger myOrderCount() function
         */
        $.ajax({
            url: "/my_order_count",
            success: function(data) {
                total_orders = data;
                myOrderCount();
            }
        });

        function myOrderCount() {
            $.ajax({
                url: "/my_order_count",
                success: function(new_orders) {
                    if (new_orders.total_orders > total_orders) {
                        document.getElementById('new_order_notification1').play();
                        Swal.fire(
                            'New Order Alert!!',
                            'Please prepare the Order.',
                            'success'
                        )
                        /**
                         * This timeout method is used to play 'new_order_notification2' music 
                         * just after 1sec of the arrival of a new order so that the user can 
                         * clearly listen 'new_order_notification1' sound
                         */
                        if (JSON.parse(new_orders.user_settings[0].settings).notification_music == 1)
                            setTimeout(function() {
                                document.getElementById('new_order_notification2').play();
                            }, 1000);
                    }
                    total_orders = new_orders.total_orders;
                    setTimeout(myOrderCount, 2000);
                }
            });
        }

        $(window).mouseover(function() {
            document.getElementById('new_order_notification2').pause();
        });

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

        function userInfoUpdate() {
            let name = $('#name').val();
            let id = $('#id').val();
            let business_name = $('#business_name').val();
            let phone = $('#phone').val();
            let business_phone = $('#business_phone').val();
            $.ajax({
                url: "{{ route('admin.userinfo.update') }}",
                type: "post",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    id: id,
                    business_name: business_name,
                    phone: phone,
                    phone: phone,
                    business_phone: business_phone,
                },
                success: function(response) {
                    if (response == "Data Sent") {
                        Swal.fire({
                            title: 'Success!',
                            text: 'We have received your modification request,our team will respond back soon after varifying',
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then(function() {
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
                    }
                }
            });
        }
        /**
         * Code for business hours form
         */
        $('#business_hours_modal').modal('show');

        // function closed(day) {
        //     let listOfClasses = document.getElementById("time[" + day + "][open]").className;
        //     if (listOfClasses.search("disabled-input-field") < 0) {
        //         // To disable the input fields
        //         document.getElementById("time[" + day + "][open]").value = null;
        //         document.getElementById("time[" + day + "][close]").value = null;
        //         // To disable the input fields
        //         document.getElementById("time[" + day + "][open]").classList.add('disabled-input-field');
        //         document.getElementById("time[" + day + "][close]").classList.add('disabled-input-field');
        //         // To remove the required attribute from the input fields 
        //         document.getElementById("time[" + day + "][open]").required = false;
        //         document.getElementById("time[" + day + "][close]").required = false;
        //     } else {
        //         // To enable the input fields
        //         document.getElementById("time[" + day + "][open]").classList.remove('disabled-input-field');
        //         document.getElementById("time[" + day + "][close]").classList.remove('disabled-input-field');
        //         // To add the required attribute from the input fields 
        //         document.getElementById("time[" + day + "][open]").required = true;
        //         document.getElementById("time[" + day + "][close]").required = true;
        //     }
        // }
    </script>
    <script src="{{ asset('res/plugins/select2/js/select2.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script> --}}
    @yield('scripts')
</body>

</html>
