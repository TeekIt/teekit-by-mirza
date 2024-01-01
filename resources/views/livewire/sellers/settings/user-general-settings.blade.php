<div>
    <div class="content">
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                {{ session()->get('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- <form action="http://127.0.0.1:8000/settings/location_update" method="POST" enctype="multipart/form-data"> --}}

        <!-- Google Map Modal - Begins -->
        <div wire:ignore.self class="modal hide" id="map_modal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Add Location</h4>
                        <button type="button" id="locationModel" class="close" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <form wire:submit.prevent="updateLocation" method="POST">
                            <div class="p-3">
                                <div class="col-md-12 my-2">
                                    <label for="full_address">Address 1</label>
                                    <input type="text" class="form-control" wire:model.defer="full_address" id="modal_address" />
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_unit_address">Address 2 (optional)</label>
                                    <input type="text" class="form-control" placeholder="Apartment, unit, suite, or floor#" wire:model.defer="unit_address" id="modal_unit_address">
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_postcode">Postcode</label>
                                    <input type="text" class="form-control" wire:model.defer="postcode" id="modal_postcode" />
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_country">Country</label>
                                    <input tyep="text" class="form-control" wire:model.defer="country" id="modal_country" />
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_state">State/Province</label>
                                    <input tyep="text" class="form-control" wire:model.defer="state" id="modal_state" />
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_city">City</label>
                                    <input type="text" class="form-control" wire:model.defer="city" id="modal_city" />
                                </div>
                                {{-- Google Maps --}}
                                <div class="col-md-12 my-2">
                                    <div style="min-height: 300px;" id="map-canvas"></div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-6">
                                        <label for="modal_lat">Lat</label>
                                        <input type="text" class="form-control" wire:model.defer="lat" id="modal_lat" />
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modal_long">Long</label>
                                        <input type="text" class="form-control" wire:model.defer="lon" id="modal_long" />
                                    </div>
                                </div>
                                <button type="submit" class="d-no mt-3 btn btn-submit btn-block btn-outline-primary" wire:loading.class="btn-dark" wire:loading.class.remove="btn-submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Update</span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Google Map Modal - Ends -->

        <!-- Edit Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Update User Info
                        </h5>
                        <button type="button" class="close" aria-label="Close" data-bs-dismiss="modal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" wire:model.defer="name" name="name" id="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ $user->name }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                    <p id="name" class="text-danger name error"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Business Name</label>
                                    <input type="text" wire:model.defer="business_name" id="business_name" class="form-control" value="{{ $user->business_name }}">
                                    <p id="business_name" class="text-danger business_name error"></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="row ">
                                        <div class="col-md-12">
                                            <input type="email" class="form-control" wire:model.defer="email" value="{{ $user->email }}">
                                            <p id="phone" class="text-danger phone error"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Business Phone</label>
                                    <div class="row ">
                                        <div class="col-md-12">
                                            <input type="text" class="form-control" id="business_phone" wire:model.defer="business_phone" value="{{ $user->business_phone }}">
                                        </div>
                                    </div>
                                    <p id="business_phone" class="text-danger business_phone error"></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <div class="row ">
                                        <div class="col-md-12">
                                            <input type="tel" class="form-control" wire:model.defer="phone" value="{{ $user->phone }}">
                                            <p id="phone" class="text-danger phone error"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer hidden ">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" wire:click="update" wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary" wire:loading.attr="disabled" wire:target="update">
                                <span wire:loading.remove wire:target="update">Save</span>
                                <span wire:loading wire:target="update">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12 col-sm-6 col-md-4">
                        <h4 class="py-2 my-1">General Settings</h4>
                    </div>
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                        <div class="card">
                            <div class="card-body-custom">
                                <div class=" d-block text-right">
                                    <div class="card-text">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form wire:submit.prevent="updateImage" enctype="multipart/form-data">
                                                    {{ csrf_field() }}
                                                    <input type="file" wire:model.defer="Image" accept="image/*" style="font-size:13px;"></label>
                                                    @error('Image')
                                                        <span class="error">{{ $message }}</span>
                                                    @enderror

                                                    <div class="row d-flex justify-content-center mt-2">
                                                        <div class="col-lg-4">
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-lg-8">
                                                            <div class="text-center">
                                                                <a style="background: #ffcf42;color:black;font-weight: 600" class="col-lg-12 col-md-12 col-sm-12 w-100 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">&nbsp;Upload&nbsp;</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>

                                                <form action="{{ route('user_img_update') }}" method="POST" enctype="multipart/form-data">
                                                    {{ csrf_field() }}
                                                    <div class="row form-inline">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>
                                                                    <img class="img img-fluid img-thumbnail" src="{{ config('constants.BUCKET') . $user->user_img }}" alt="No Image Uploaded">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <table class="w-100">
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text" name="name" value="{{ $user->name }} {{ $user->l_name }}" class="form-control w-100" disabled />
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text" name="email" value="{{ $user->email }}" class="form-control w-100" disabled />
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text" name="business_name" value="{{ $user->business_name }}" class="form-control w-100" disabled />
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text" name="phone" value="{{ $user->phone }}" class="form-control w-100" disabled />
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="row d-flex justify-content-center mt-2">
                                                            <div class="col-lg-4"></div>
                                                            <div class="col-lg-8">
                                                                <div class="text-center">
                                                                    <a style="background: #ffcf42;color:black;font-weight: 600" class="col-lg-12 w-100 pb-2 border-0 btn btn-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="event.preventDefault();">&nbsp;Edit&nbsp;</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row d-flex justify-content-center my-2">
                                                            <div class="col-md-12">
                                                                <div class="row form-inline">
                                                                    <div class="col-md-4">
                                                                        <label>Set Location</label>
                                                                    </div>
                                                                    <div class="col-md-8 my-2">
                                                                        <div class="form-group">
                                                                            <i class="fas fa-map-marked-alt text-primary fa-2x mt-2"></i>
                                                                            <span class="mx-3 mt-2">
                                                                                {{ $user->full_address }}, {{ $user->city }}, {{ $user->state }}, {{ $user->country }}, {{ $user->postcode }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-4"></div>
                                                                    <div class="col-lg-8">
                                                                        <div class="text-center">
                                                                            <a style="background: #ffcf42;color:black;font-weight: 600" class="col-12 w-100 pb-2 border-0 btn btn-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#map_modal">
                                                                                Edit Location
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form action="{{ route('password_update') }}" method="POST">
                                                    {{ csrf_field() }}
                                                    <div class="row form-inline justify-content-center">
                                                        <div class="col-md-4">
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-md-8 justify-content-center">
                                                            <div class="form-group">
                                                                <input type="password" class="form-control w-100" wire:model.defer="old_password" placeholder="Old Password" required wire:model.defer="old_password" minlength="8">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <input type="password" class="form-control w-100" wire:model.defer="new_password" placeholder="New Password" required wire:model.defer="new_password" minlength="8">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="row d-flex justify-content-center mt-4">
                                            {{-- Update --}}
                                            <div class="col-lg-4 col-sm-2">
                                                &nbsp;
                                            </div>
                                            <div class="col-lg-8 col-sm-10">
                                                <div class="text-center">
                                                    <button style="background: #ffcf42;color:black;font-weight: 600" class="col-12 pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" wire:click="passwordUpdate" type="submit">Update</button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Export --}}
                                        <div class="row my-2">
                                            <label class="col-lg-4">
                                                Export Details
                                            </label>
                                            <div class="col-lg-8">
                                                <div class="text-center">
                                                    <a wire:click="exportProducts" style="background: #3a4b83;color:white;font-weight: 600" class="col-12 pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">Export</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- </form> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</div><!-- /.container-fluid -->
<div class="container-fluid">
    <div class="row">
        <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
            <h4 class="text-left text-primary">Update Store Hours</h4>
            <div class="card">
                <div class="card-body-custom">
                    <div class=" d-block text-right">
                        <div class="card-text">
                            <div class="row">
                                <div class="col-md-12">
                                    <form action="{{ route('time_update') }}" method="POST" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="row form-inline">
                                            <div class="col-md-2 col-2">
                                                <div class="form-group">
                                                    <label>Day &emsp;</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-4">
                                                <div class="form-group">
                                                    <label>Opening Time &emsp;</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-4">
                                                <div class="form-group">
                                                    <label>Closing Time &emsp;</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-2">
                                                <div class="form-group">
                                                    <label>Closed &emsp;</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            $bh = json_decode($user->business_hours, true);
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            for ($i = 0; $i < count($days); $i++) {
                                            ?>
                                        <!-- Day & Time Sect Begin -->
                                        <div class="row form-inline">
                                            <div class="col-md-2 col-3">
                                                <div class="form-group">
                                                    <p class="day">{{ $days[$i] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-4">
                                                <div class="form-group">
                                                    <input type="text" name="time[{{ $days[$i] }}][open]" id="time[{{ $days[$i] }}][open]" value="<?php echo isset($bh['time'][$days[$i]]['open']) ? $bh['time'][$days[$i]]['open'] : ''; ?>" class="stimepicker form-control <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'disabled-input-field' : ''; ?>" <?php echo isset($bh['time'][$days[$i]]['closed']) ? '' : 'required'; ?>>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-4">
                                                <div class="form-group">
                                                    <input type="text" name="time[{{ $days[$i] }}][close]" id="time[{{ $days[$i] }}][close]" value="<?php echo isset($bh['time'][$days[$i]]['close']) ? $bh['time'][$days[$i]]['close'] : ''; ?>" class="etimepicker form-control <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'disabled-input-field' : ''; ?>" <?php echo isset($bh['time'][$days[$i]]['closed']) ? '' : 'required'; ?>>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-1">
                                                <div class="form-group">
                                                    &emsp;
                                                    <input type="checkbox" name="time[{{ $days[$i] }}][closed]" onclick="closed('<?php echo $days[$i]; ?>')" <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Day & Time Sect End -->
                                        <?php
                                            }
                                            ?>

                                        <div class="col-md-12 text-center">
                                            <button style="background: #ffcf42;color:black;font-weight: 600" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">{{ __('Update') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</div>
<!-- /.container-fluid -->
</div>
<!-- /.content -->
</div>
</div>
