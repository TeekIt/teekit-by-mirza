<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
    @livewireStyles
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        {{-- 1 == Super Admin --}}
        @if (Auth::user()->role_id === 1)
            @include('layouts.admin.navbar')
            @include('layouts.admin.sidebar')
            {{-- 2 == Parent Seller, 5 == Child Seller --}}
        @elseif(Auth::user()->role_id == 2 || Auth::user()->role_id == 5)
            @include('layouts.shopkeeper.navbar')
            @include('layouts.shopkeeper.sidebar')
            <x-seller-business-hours-modal/>
        @endif

        <div class="content-wrapper">
            <!-- Livewire component will render here by default -->
            {{ $slot }}
        </div>
    </div>
    <audio id="newOrderNotification1">
        <source src="{{ asset('audio/TeekItaa.mp4') }}" type="audio/mp4">
    </audio>
    <audio id="newOrderNotification2" loop>
        <source src="{{ asset('audio/TeekItNotificationMusic (mp3cut.net).mp3') }}" type="audio/mp3">
    </audio>
    @include('layouts.scripts')
    @livewireScripts
</body>

</html>
