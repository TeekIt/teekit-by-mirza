@extends('layouts.auth.app')

@section('content')
    <style>
        .password-reset-btn {
            display: block;
            width: 100%;
            margin-top: 15px !important;
            background: #ffec00;
            border: 0;
            border-radius: 0;
            color: #000100;
            font-weight: 600;
        }
    </style>
    <div class="rounded-5 px-5 py-5 bg-light shadow">
        <div class="col-md-12">
            <h1 class="text-site-primary fs-3">Reset Password</h1>
            <form method="POST" action="{{ route('password.update') }}" class="mt-3">
                {{ csrf_field() }}
                <input type="hidden" name="token" value="{{ $token }}"
                    class="form-control {{ $errors->has('token') ? 'is-invalid' : '' }}">
                @error('token')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="email" placeholder="Email" type="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            name="email" value="{{ $email ?? old('email') }}" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="password" type="password"
                            class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            placeholder="Password" name="password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="password-confirm" type="password" class="form-control"
                            placeholder="Confirm Password" name="password_confirmation">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary password-reset-btn">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
