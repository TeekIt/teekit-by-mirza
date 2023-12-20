@extends('layouts.auth.app')

@section('content')
    @include('components.google-map-modal')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-white text-center">{{ __('Sign Up') }}</h1>
                <form id="sign_up_form" style="margin-bottom: 100px;" onsubmit="return false">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="name" type="text" placeholder="Name" class="form-control signup-input-fields{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" autofocus>
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="name" class="text-danger name error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="email" type="email" placeholder="Email" class="form-control signup-input-fields{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" autofocus>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="email" class="text-danger email error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="password" placeholder="Password" type="password" class="form-control signup-input-fields{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" minlength="8" autocomplete="true">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="password" class="text-danger password error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12 input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+44</span>
                            </div>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" placeholder="Phone Number" class="form-control signup-input-fields{{ $errors->has('phone') ? ' is-invalid' : '' }}" id="phone" name="phone" value="{{ old('phone') }}" autofocus>
                            @if ($errors->has('phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('phone') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="phone" class="text-danger phone error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="company_name" type="text" placeholder="Company Name" class="form-control signup-input-fields{{ $errors->has('company_name') ? ' is-invalid' : '' }}" name="company_name" value="{{ old('company_name') }}" autofocus>
                            @if ($errors->has('company_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('company_name') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="company_name" class="text-danger company_name error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12 input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+44</span>
                            </div>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" placeholder="Company Phone" class="form-control signup-input-fields{{ $errors->has('company_phone') ? ' is-invalid' : '' }}" id="company_phone" name="company_phone" value="{{ old('company_phone') }}" autofocus>
                            @if ($errors->has('company_phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('company_phone') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="company_phone" class="text-danger company_phone error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="form-group" data-bs-toggle="modal" data-bs-target="#map_modal" style="cursor: pointer;">
                                <i class="fas fa-map-marked-alt text-light fa-2x"></i>
                                &nbsp;&nbsp;&nbsp;
                                <span class="text-light" id="form_location_text">Set Location</span>
                                <input type="hidden" id="user_address" name="user_address">
                                <input type="hidden" id="user_country" name="user_country">
                                <input type="hidden" id="user_state" name="user_state">
                                <input type="hidden" id="user_city" name="user_city">
                                <input type="hidden" id="address[lat]" name="address[lat]">
                                <input type="hidden" id="address[lon]" name="address[lon]">
                            </div>
                        </div>
                        <p id="company_phone" class="text-danger location error"></p>
                    </div>
                    <label for="chkSelect" class="text-light">
                        <input type="checkbox" name="checked_value" id="chkSelect" onclick="return checkbox()" />
                        I'm a child store
                    </label>
                    <?php $stores = App\User::where('role_id', 2)->get(); ?>
                    <div class="form-group row ">
                        <div class="col-md-12 mt-0">
                            <div class="form-group text-light" id="content" style="display:none">
                                <select class="form-control signup-input-fields text-light" id="parent_store" name="parent_store">
                                    <option value="" selected>Select your parent store</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->business_name }}">{{ $store->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="text-danger parent_store error"></p>
                    </div>
                    <div class="form-group row mb-0">
                        <div class="col-md-12">
                            <button class="btn btn-outline-primary my-2 my-sm-0 signup-btn" type="submit" id="signup" onclick="signUp()">
                                Sign up
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
