@extends('layouts.auth.app')

@php
    use App\Models\User;
@endphp

@section('content')
    <style>
        .country-code {
            max-width: 64px;
        }
    </style>

    @include('components.google-map-modal')
    <div class="rounded-5 px-5 pt-4 mb-5 bg-light shadow">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-site-primary py-3">Sign Up</h1>
                <form style="margin-bottom: 100px;" onsubmit="return false">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="name" type="text" placeholder="Name"
                                class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                value="{{ old('name') }}" autofocus>
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
                            <input id="email" type="email" placeholder="Email"
                                class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                value="{{ old('email') }}" autofocus>
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
                            <input id="password" placeholder="Password" type="password"
                                class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                                minlength="8" autocomplete="true">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p id="password" class="text-danger password error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-12 input-group">
                            <select class="form-control country-code" name="country_code" id="country_code"
                                onchange="countryCodeChanged(this.value)">
                                <option value="+44">+44</option>
                                <option value="+92">+92</option>
                            </select>
                            <input type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                maxlength="10" placeholder="Phone Number"
                                class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}" id="phone"
                                name="phone" value="{{ old('phone') }}" autofocus>
                            @if ($errors->has('phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('phone') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p class="text-danger country_code error"></p>
                        <p class="text-danger phone error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <input type="text" placeholder="Business Name"
                                class="form-control {{ $errors->has('business_name') ? ' is-invalid' : '' }}"
                                id="business_name" name="business_name" value="{{ old('business_name') }}" autofocus>
                            @if ($errors->has('business_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('business_name') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p class="text-danger business_name error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-12 input-group">
                            <input type="text" class="form-control country-code" id="business_phone_country_code"
                                value="+44" disabled>
                            <input type="text" placeholder="Business Phone"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                maxlength="10"
                                class="form-control {{ $errors->has('business_phone') ? ' is-invalid' : '' }}"
                                id="business_phone" name="business_phone" value="{{ old('business_phone') }}" autofocus>
                            @if ($errors->has('business_phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('business_phone') }}</strong>
                                </span>
                            @endif
                        </div>
                        <p class="text-danger business_phone error"></p>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <div class="form-group" data-bs-toggle="modal" data-bs-target="#map_modal"
                                style="cursor: pointer;">
                                <i class="fas fa-map-marked-alt text-site-primary fa-2x"></i>
                                &nbsp;&nbsp;&nbsp;
                                <span id="display_location">Set Location</span>
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
                    <label for="is_child_seller">
                        <input type="checkbox" name="is_child_seller" id="is_child_seller"
                            onclick="checkbox()" />
                        I'm a child store
                    </label>
                    <div class="form-group row">
                        <div class="col-12 mt-0">
                            <div class="form-group" id="parent_stores_list" style="display:none">
                                <select class="form-control" id="parent_store" name="parent_store">
                                    <option value="" selected>Select your parent store</option>
                                    @foreach (User::getParentSellersSpecificColumns(['business_name']) as $store)
                                        <option value="{{ $store->business_name }}">{{ $store->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="text-danger parent_store error"></p>
                    </div>
                    <div class="form-group row mb-0">
                        <div class="col-12">
                            <button class="btn btn-outline-primary my-2 my-sm-0 signup-btn" type="submit"
                                id="signup-btn" onclick="signUp()">
                                Sign Up
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const checkbox = () => {
            $("#is_child_seller").change(function() {
                if ($(this).is(":checked")) {
                    $("#parent_stores_list").show();
                } else {
                    $("#parent_stores_list").hide();
                }
            });
        }

        const countryCodeChanged = (selectedCountryCode) => {
            document.getElementById('business_phone_country_code').value = selectedCountryCode;
        }

        const signUp = () => {
            let spinner =
                '<div class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"></div></div>';
            let name = $('#name').val();
            let email = $('#email').val();
            let password = $('#password').val();
            let country_code = $('#country_code').val();
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
            let is_child_seller = 0;

            if ($('#is_child_seller').is(':checked')) {
                is_child_seller = 1;
            }

            $('#signup-btn').html(spinner);

            $.ajax({
                url: "{{ route('register') }}",
                type: "post",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    email: email,
                    password: password,
                    country_code: country_code,
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
                    is_child_seller: is_child_seller
                },
                success: function(response) {
                    $('#signup-btn').text('Sign Up');
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

                        const errors = response.message;

                        const errorMap = {
                            name: '.name',
                            email: '.email',
                            password: '.password',
                            country_code: '.country_code',
                            phone: '.phone',
                            business_name: '.business_name',
                            business_phone: '.business_phone',
                            address: '.location',
                            postcode: '.location',
                            country: '.location',
                            state: '.location',
                            city: '.location'
                        };

                        Object.keys(errorMap).forEach(field => {
                            if (errors[field]) {
                                $(errorMap[field]).html(errors[field][0]);
                            }
                        });

                        if ($('#is_child_seller').is(':checked') && errors.parent_store) {
                            $('.parent_store').html(errors.parent_store[0]);
                        }
                    }
                }
            });
        }
    </script>
@endsection
