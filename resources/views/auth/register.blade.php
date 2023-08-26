{{-- @extends('layouts.auth.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-white text-center">{{ __('Sign up') }}</h1>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="name" type="text" placeholder="Name"
                            class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                            value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="email" type="email" placeholder="Email"
                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                            value="{{ old('email') }}" required autofocus>

                        @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="password" placeholder="Password" type="password"
                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                            required>

                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="phone" type="text" placeholder="Phone Number"
                            class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" name="phone"
                            value="{{ old('phone') }}" required autofocus>

                        @if ($errors->has('phone'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('phone') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="company_name" type="text" placeholder="Company Name"
                            class="form-control{{ $errors->has('company_name') ? ' is-invalid' : '' }}"
                            name="company_name" value="{{ old('company_name') }}" required autofocus>

                        @if ($errors->has('company_name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('company_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="company_phone" type="text" placeholder="Company Phone"
                            class="form-control{{ $errors->has('company_phone') ? ' is-invalid' : '' }}"
                            name="company_phone" value="{{ old('company_phone') }}" required autofocus>

                        @if ($errors->has('company_phone'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('company_phone') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">

                        <button class="btn btn-outline-primary my-2 my-sm-0" type="submit"
                            style="/* padding: 5px 25px; */display: block;width: 100%;margin-top: 15px!important;background: #ffec00;border: 0;border-radius: 0;color: #000100;font-weight: 600;border: 0; ">Sign
                            up</button>

                        <a class="btn btn-link text-white" href="{{ route('login') }}">
                            {{ __('Already have an account?') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection --}}