@extends('layouts.auth.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-white text-center">{{ __('Sign Up') }}</h1>
                <form id="sign_up_form" style="margin-bottom: 100px;" onsubmit="return false">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input id="name" type="text" placeholder="Name"
                                class="form-control signup-input-fields{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                name="name" value="{{ old('name') }}" autofocus>

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
                                class="form-control signup-input-fields{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                name="email" value="{{ old('email') }}" autofocus>
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
                                class="form-control signup-input-fields{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                name="password" minlength="8">
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
                            <input type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                maxlength="10" placeholder="Phone Number"
                                class="form-control signup-input-fields{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                id="phone" name="phone" value="{{ old('phone') }}" autofocus>
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
                            <input id="company_name" type="text" placeholder="Company Name"
                                class="form-control signup-input-fields{{ $errors->has('company_name') ? ' is-invalid' : '' }}"
                                name="company_name" value="{{ old('company_name') }}" autofocus>
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
                            <input type="text"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                maxlength="10" placeholder="Company Phone"
                                class="form-control signup-input-fields{{ $errors->has('company_phone') ? ' is-invalid' : '' }}"
                                id="company_phone" name="company_phone" value="{{ old('company_phone') }}" autofocus>
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
                            <div class="form-group" data-bs-toggle="modal" data-bs-target="#map_modal"
                                style="cursor: pointer;">
                                <i class="fas fa-map-marked-alt text-light fa-2x"></i>
                                &nbsp;&nbsp;&nbsp;
                                <span class="text-light" id="user_location">Set Location</span>
                                <input type="hidden" id="location_text" name="location_text">
                                <input type="hidden" id="Address[lat]" name="Address[lat]">
                                <input type="hidden" id="Address[lon]" name="Address[lon]">
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
                                <label for=""></label>
                                <select class="form-control signup-input-fields" id="select_values" name="select_values">
                                    <option value="" selected>Select your parent store</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->name }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="text-danger select_values error"></p>
                    </div>
                    <div class="form-group row mb-0">
                        <div class="col-md-12">
                            <button class="btn btn-outline-primary my-2 my-sm-0 signup-btn" type="submit"
                                id="signup" onclick="signUp()">Sign up</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Google Map Modal - Begins -->
    <div class="modal hide" id="map_modal">
        <div class="modal-dialog modal-lg  modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add Location</h4>
                    <button type="button" id="locationModel" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="card">
                            <div class="card-body-custom">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12 mt-3 mb-3">
                                            <div class="form-group" style="height:100%; width:100%">
                                                <label for="Address">Address</label>
                                                <input type="text" class="form-control signup-input-fields"
                                                    name="modal_location_text" id="modal_location_text" />
                                                <div class="mt-3 mb-3"
                                                    style="height: 100%; width: 100%; margin: 0px; padding: 0px;min-height: 200px;"
                                                    id="map-canvas"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                            <br>
                                            <br>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Address">Lat</label>
                                            <input type="text" name="ad_lat" id="ad_lat"
                                                class="form-control signup-input-fields">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Address">Long</label>
                                            <input type="text" name="ad_long" id="ad_long"
                                                class="form-control signup-input-fields">
                                        </div>
                                        <button type="submit" onclick="submitLocation()"
                                            class="d-no mt-3 btn btn-submit btn-block btn-outline-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
    <!-- Google Map Modal - Ends -->
@endsection
