<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1" name="viewport">
<meta content="ie=edge" http-equiv="x-ua-compatible">
<link rel="icon" href="{{ asset('res/res/img/logo.png') }}" type="image/svg+xml" />
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<!-- Font Awesome Icons - 5.13.0 -->
<link href="{{ asset('res/plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
<!-- Theme style - Bootstrap 4 CSS -->
<link href="{{ asset('res/dist/css/adminlte.min.css') }}" rel="stylesheet">
<!-- Bootstrap 5 CSS -->
<link href="{{ asset('bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
{{-- <link href="{{ asset('res/plugins/select2/css/select2.min.css') }}" rel="stylesheet"> --}}
<!-- Custom CSS -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{asset('css/custom-styles.css')}}" rel="stylesheet">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<!-- JQuery Time Picker CSS -->
<link rel="stylesheet" href="{{ asset('res/dist/css/jquery.timepicker.min.css') }}">