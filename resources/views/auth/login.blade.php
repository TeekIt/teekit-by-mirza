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
                            <input type="text" placeholder="Business Name" class="form-control signup-input-fields{{ $errors->has('business_name') ? ' is-invalid' : '' }}" id="business_name" name="business_name" value="{{ old('business_name') }}" autofocus>
                            @if ($errors->has('business_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('business_name') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p class="text-danger business_name error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12 input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+44</span>
                            </div>
                            <input type="text" placeholder="Business Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" class="form-control signup-input-fields{{ $errors->has('business_phone') ? ' is-invalid' : '' }}" id="business_phone" name="business_phone" value="{{ old('business_phone') }}" autofocus>
                            @if ($errors->has('business_phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('business_phone') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p class="text-danger business_phone error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="form-group" data-bs-toggle="modal" data-bs-target="#map_modal" style="cursor: pointer;">
                                <i class="fas fa-map-marked-alt text-light fa-2x"></i>
                                &nbsp;&nbsp;&nbsp;
                                <span class="text-light" id="display_location">Set Location</span>
                                <input type="hidden" id="address" name="address">
                                <input type="hidden" id="unit_address" name="unit_address">
                                <input type="hidden" id="postcode" name="postcode">
                                <input type="hidden" id="country" name="country">
                                <input type="hidden" id="state" name="state">
                                <input type="hidden" id="city" name="city">
                                <input type="hidden" id="address[lat]" name="address[lat]">
                                <input type="hidden" id="address[lon]" name="address[lon]">
                            </div>
                        </div>
                        <p class="text-danger location error"></p>
                    </div>
                    <label for="chkSelect" class="text-light">
                        <input type="checkbox" name="checked_value" id="chkSelect" onclick="return checkbox()" />
                        I'm a child store
                    </label>
                    <div class="form-group row">
                        <div class="col-md-12 mt-0">
                            <div class="form-group text-light" id="content" style="display:none">
                                <select class="form-control signup-input-fields text-light" id="parent_store" name="parent_store">
                                    <option value="" selected>Select your parent store</option>
                                    @foreach (App\User::getParentSellersSpecificColumns(['business_name']) as $store)
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
                                Sign Up
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const signUp = () => {
            let spinner = '<div class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
            let name = $('#name').val();
            let email = $('#email').val();
            let password = $('#password').val();
            let phone = $('#phone').val();
            let business_name = $('#business_name').val();
            let business_phone = $('#business_phone').val();
            let address = $('#address').val();
            let unit_address = $('#unit_address').val();
            let postcode = $('#postcode').val();
            let country = $('#country').val();
            let state = $('#state').val();
            let city = $('#city').val();
            let lat = $('input[id="address[lat]"]').val();
            let lon = $('input[id="address[lon]"]').val();
            let parent_store = $('#parent_store').val();
            let checked_value = 0;
            if ($('#chkSelect').is(':checked')) {
                checked_value = 1;
            }
            $('#signup').html(spinner);
            $.ajax({
                url: "{{ route('register') }}",
                type: "post",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    email: email,
                    password: password,
                    phone: phone,
                    business_name: business_name,
                    business_phone: business_phone,
                    address: address,
                    unit_address: unit_address,
                    postcode: postcode,
                    country: country,
                    state: state,
                    city: city,
                    lat: lat,
                    lon: lon,
                    parent_store: parent_store,
                    checked_value: checked_value
                },
                success: function(response) {
                    $('#signup').text('Sign up');
                    if (response == "User Created") {
                        Swal.fire({
                            title: 'Success!',
                            text: 'We have received your store details we will contact you soon to verify your store',
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        $('.error').html('');
                        if (response.errors.name) {
                            $('.name').html('');
                            $('.name').html(response.errors.name[0]);
                        }
                        if (response.errors.email) {
                            $('.email').html(response.errors.email[0]);
                        }
                        if (response.errors.password) {
                            $('.password').html(response.errors.password[0]);
                        }
                        if (response.errors.phone) {
                            $('.phone').html(response.errors.phone[0]);
                        }
                        if (response.errors.business_name) {
                            $('.business_name').html(response.errors.business_name[0]);
                        }
                        if (response.errors.business_phone) {
                            $('.business_phone').html(response.errors.business_phone[0]);
                        }
                        if (response.errors.address) {
                            $('.location').html(response.errors.address[0]);
                        }
                        if (response.errors.postcode) {
                            $('.location').html(response.errors.postcode[0]);
                        }
                        if (response.errors.country) {
                            $('.location').html(response.errors.country[0]);
                        }
                        if (response.errors.state) {
                            $('.location').html(response.errors.state[0]);
                        }
                        if (response.errors.city) {
                            $('.location').html(response.errors.city[0]);
                        }
                        if ($('#chkSelect').is(":checked")) {
                            if (response.errors.parent_store) {
                                $('.parent_store').html(response.errors.parent_store[0]);
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
