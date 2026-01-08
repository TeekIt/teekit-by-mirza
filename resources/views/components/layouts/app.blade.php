@php
    use App\Enums\UserRoleEnum;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
    @livewireStyles
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        @include('layouts.common.navbar')

        @if (Auth::user()->role_id == UserRoleEnum::SUPERADMIN->value)
            @include('layouts.admin.sidebar')
        @elseif(Auth::user()->role_id == UserRoleEnum::SELLER->value || Auth::user()->role_id == UserRoleEnum::CHILD_SELLER->value)
            @include('layouts.shopkeeper.sidebar')
            <x-seller-business-hours-modal />
        @endif

        <div class="content-wrapper">
            <!-- Livewire components will render here by default -->
            {{ $slot }}
        </div>
    </div>
    <audio id="newOrderNotification1">
        <source src="{{ asset('audio/TeekItaa.mp4') }}" type="audio/mp4">
    </audio>
    <audio id="newOrderNotification2">
        <source src="{{ asset('audio/TeekItNotificationMusic (mp3cut.net).mp3') }}" type="audio/mp3">
    </audio>
    
    @include('layouts.scripts')
    @livewireScripts
</body>

</html>
