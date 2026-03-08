@extends('layouts.auth.app')

@section('content')
    <style>
        .send-reset-link-btn {
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
        <div class="w-100">
            <h1 class="text-site-primary fs-3">Reset Password Email</h1>
            <form method="POST" action="{{ route('password.email') }}" class="mt-3">
                {{ csrf_field() }}
                <div class="form-group row">
                    <div class="col-md-12">
                        <input type="email" id="email" placeholder="Please enter your email"
                            class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary send-reset-link-btn">
                            Send Password Reset Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
