<div>
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="content">
        
        <x-session-messages />

        <!-- Google Map Modal -->
        <div wire:ignore.self class="modal hide" id="mapModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Add Location</h4>
                        <button type="button" id="locationModel" class="close"
                            data-bs-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <form action="{{ route('seller.settings.update.location') }}" method="POST">
                            @csrf
                            <div class="p-3">
                                <div class="col-md-12 my-2">
                                    <label for="full_address">Address 1</label>
                                    <input type="text" class="form-control" name="full_address" id="modal_address" value="{{ Str::limit($user->full_address, 50) }}"/>
                                    <small class="text-danger">
                                        @error('full_address')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_unit_address">Address 2 (optional)</label>
                                    <input type="text" class="form-control"
                                        placeholder="Apartment, unit, suite, or floor#" name="unit_address"
                                        id="modal_unit_address" value="{{ $user->unit_address }}">
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_postcode">Postcode</label>
                                    <input type="text" class="form-control" name="postcode" id="modal_postcode"
                                        value="{{ $user->postcode }}" />
                                    <small class="text-danger">
                                        @error('postcode')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_country">Country</label>
                                    <input type="text" class="form-control" name="country" id="modal_country"
                                        value="{{ $user->country }}" />
                                    <small class="text-danger">
                                        @error('country')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_state">State/Province</label>
                                    <input type="text" class="form-control" name="state" id="modal_state"
                                        value="{{ $user->state }}" />
                                    <small class="text-danger">
                                        @error('state')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-md-12 my-2">
                                    <label for="modal_city">City</label>
                                    <input type="text" class="form-control" name="city" id="modal_city"
                                        value="{{ $user->city }}" />
                                    <small class="text-danger">
                                        @error('city')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                {{-- Google Maps --}}
                                <div class="col-md-12 my-2" wire:ignore>
                                    <div style="min-height: 300px;" id="map-canvas"></div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-6">
                                        <label for="modal_lat">Lat</label>
                                        <input type="text" class="form-control" name="lat" id="modal_lat"
                                            value="{{ $user->lat }}" />
                                        <small class="text-danger">
                                            @error('lat')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modal_long">Long</label>
                                        <input type="text" class="form-control" name="lon" id="modal_long"
                                            value="{{ $user->lon }}" />
                                        <small class="text-danger">
                                            @error('lon')
                                                {{ $message }}
                                            @enderror
                                        </small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-site-primary mt-3 w-100">
                                    Update
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
        <!-- Edit Modal -->
        <div wire:ignore.self class="modal fade" id="editUserModal" tabindex="-1" role="dialog"
            aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Update Your Info
                        </h5>
                        <button type="button" class="close" wire:click="resetModal" aria-label="Close"
                            data-bs-dismiss="modal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label>Your Name</label>
                                    <div class="input-group">
                                        <input type="text" wire:model.defer="name" class="form-control"
                                            placeholder="Enter your name">
                                        <button type="button" class="btn btn-site-primary" wire:click="updateName"
                                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                                            wire:loading.attr="disabled" wire:target="updateName">
                                            <span wire:loading.remove wire:target="updateName">Update</span>
                                            <span wire:loading wire:target="updateName">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <small class="text-danger">
                                        @error('name')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Business Name</label>
                                    <div class="input-group">
                                        <input type="text" wire:model.defer="business_name" class="form-control"
                                            placeholder="Enter your business name">
                                        <button type="button" class="btn btn-site-primary"
                                            wire:click="updateBusinessName" wire:loading.class="btn-dark"
                                            wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled"
                                            wire:target="updateBusinessName">
                                            <span wire:loading.remove wire:target="updateBusinessName">Update</span>
                                            <span wire:loading wire:target="updateBusinessName">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <small class="text-danger">
                                        @error('business_name')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" wire:model.defer="email"
                                            placeholder="Enter your email address">
                                        <button type="button" class="btn btn-site-primary" wire:click="updateEmail"
                                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                                            wire:loading.attr="disabled" wire:target="updateEmail">
                                            <span wire:loading.remove wire:target="updateEmail">Update</span>
                                            <span wire:loading wire:target="updateEmail">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <small class="text-danger">
                                        @error('email')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Business Phone</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model.defer="business_phone"
                                            placeholder="Enter your business number">
                                        <button type="button" class="btn btn-site-primary"
                                            wire:click="updateBusinessPhone" wire:loading.class="btn-dark"
                                            wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled"
                                            wire:target="updateBusinessPhone">
                                            <span wire:loading.remove wire:target="updateBusinessPhone">Update</span>
                                            <span wire:loading wire:target="updateBusinessPhone">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <small class="text-danger">
                                        @error('business_phone')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Personal Phone</label>
                                    <div class="input-group">
                                        <input type="tel" class="form-control" wire:model.defer="phone"
                                            placeholder="Enter your phone number">
                                        <button type="button" class="btn btn-site-primary" wire:click="updatePhone"
                                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                                            wire:loading.attr="disabled" wire:target="updatePhone">
                                            <span wire:loading.remove wire:target="updatePhone">Update</span>
                                            <span wire:loading wire:target="updatePhone">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <small class="text-danger">
                                        @error('phone')
                                            {{ $message }}
                                        @enderror
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetModal"
                            data-bs-dismiss="modal">Close</button>
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
                                                <div class="row form-inline">
                                                    <div class="col-12 col-lg-4">
                                                        <img class="img img-fluid img-thumbnail"
                                                            src="{{ config('constants.BUCKET') . $user->user_img }}"
                                                            alt="Store image not uploaded :(">
                                                        <form wire:submit.prevent="updateImage">
                                                            <input type="file" class="my-3"
                                                                wire:model.defer="image_to_upload" accept="image/*">
                                                            <div class="col-12 my-1">
                                                                <div class="text-center">
                                                                    <button type="submit"
                                                                        class="w-100 pb-2 border-0 btn rounded-pill cstm-edit-btn"
                                                                        wire:loading.class="btn-dark"
                                                                        wire:loading.class.remove="cstm-edit-btn"
                                                                        wire:loading.attr="disabled"
                                                                        wire:target="updateImage">
                                                                        <span wire:loading.remove
                                                                            wire:target="updateImage">Upload</span>
                                                                        <span wire:loading wire:target="updateImage">
                                                                            <span
                                                                                class="spinner-border spinner-border-sm text-light"
                                                                                role="status" aria-hidden="true">
                                                                            </span>
                                                                        </span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 col-lg-8">
                                                        <div class="form-group">
                                                            <table class="w-100">
                                                                <tr>
                                                                    <td>
                                                                        <input type="text"
                                                                            value="{{ $user->name }} {{ $user->l_name }}"
                                                                            class="form-control w-100" disabled />
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <input type="text"
                                                                            value="{{ $user->business_name }}"
                                                                            class="form-control w-100" disabled />
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <input type="text"
                                                                            value="{{ $user->email }}"
                                                                            class="form-control w-100" disabled />
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <input type="text"
                                                                            value="{{ $user->business_phone }}"
                                                                            class="form-control w-100" disabled />
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <input type="text"
                                                                            value="{{ $user->phone }}"
                                                                            class="form-control w-100" disabled />
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="row d-flex justify-content-center mt-2">
                                                        <div class="col-lg-4"></div>
                                                        <div class="col-lg-8">
                                                            <div class="text-center">
                                                                <button type="button"
                                                                    class="col-12 px-5 py-2 border-0 rounded-pill cstm-edit-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editUserModal">
                                                                    Edit Profile
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row d-flex justify-content-center my-2">
                                                        <div class="col-md-12">
                                                            <div class="row form-inline">
                                                                <div class="col-12 col-lg-4 mt-2">
                                                                    <label>Set Location</label>
                                                                </div>
                                                                <div class="col-12 col-lg-8 my-2">
                                                                    <div class="form-group">
                                                                        <table class="w-100">
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ Str::limit($user->full_address, 50) }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ $user->unit_address }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ $user->postcode }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ $user->country }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ $user->state }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        value="{{ $user->city }}"
                                                                                        class="form-control w-100"
                                                                                        disabled />
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-lg-4"></div>
                                                                <div class="col-lg-8">
                                                                    <div class="text-center">
                                                                        <button type="button"
                                                                            class="col-12 px-5 py-2 border-0 rounded-pill cstm-edit-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#mapModal">
                                                                            Edit Location
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row form-inline justify-content-center">
                                                                <div class="col-12 col-lg-4 mt-2">
                                                                    <label>Reset Password</label>
                                                                </div>
                                                                <div
                                                                    class="col-12 col-lg-8 my-2 justify-content-center">
                                                                    <div class="form-group">
                                                                        <input type="password"
                                                                            class="form-control w-100"
                                                                            wire:model.defer="old_password"
                                                                            placeholder="Old Password" required
                                                                            wire:model.defer="old_password"
                                                                            minlength="8">

                                                                        <input type="password"
                                                                            class="form-control w-100"
                                                                            wire:model.defer="new_password"
                                                                            placeholder="New Password" required
                                                                            wire:model.defer="new_password"
                                                                            minlength="8">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row d-flex justify-content-center mt-4">
                                                        <div class="col-lg-4">
                                                        </div>
                                                        <div class="col-lg-8">
                                                            <div class="text-center">
                                                                <button type="button"
                                                                    class="col-12 px-5 py-2 border-0 rounded-pill cstm-edit-btn"
                                                                    wire:click="passwordUpdate"
                                                                    wire:loading.class="btn-dark"
                                                                    wire:loading.class.remove="cstm-edit-btn"
                                                                    wire:loading.attr="disabled">
                                                                    <span wire:loading.remove
                                                                        wire:target="passwordUpdate">Reset</span>
                                                                    <span wire:loading wire:target="passwordUpdate">
                                                                        <span
                                                                            class="spinner-border spinner-border-sm text-light"
                                                                            role="status" aria-hidden="true">
                                                                        </span>
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{--
                                                        Please do not remove the following code
                                                        As we may require this in the future
                                                    --}}
                                                    {{-- <div class="row my-2">
                                                        <label class="col-lg-4">
                                                            Export Products
                                                        </label>
                                                        <div class="col-lg-8">
                                                            <div class="text-center">
                                                                <button type="submit"
                                                                    class="col-12 px-5 py-2 border-0 rounded-pill cstm-edit-btn"
                                                                    wire:loading.class="btn-dark"
                                                                    wire:loading.class.remove="cstm-edit-btn"
                                                                    wire:loading.attr="disabled"
                                                                    wire:click="exportProducts">
                                                                    <span wire:loading.remove
                                                                        wire:target="exportProducts">Export</span>
                                                                    <span wire:loading wire:target="exportProducts">
                                                                        <span
                                                                            class="spinner-border spinner-border-sm text-light"
                                                                            role="status" aria-hidden="true">
                                                                        </span>
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                </div>
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
                                                <form action="{{ route('seller.update.required.info') }}"
                                                    method="POST" enctype="multipart/form-data">
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
                                                                <input type="text"
                                                                    name="time[{{ $days[$i] }}][open]"
                                                                    id="time[{{ $days[$i] }}][open]"
                                                                    value="<?php echo isset($bh['time'][$days[$i]]['open']) ? $bh['time'][$days[$i]]['open'] : ''; ?>"
                                                                    class="stimepicker form-control <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'disabled-input-field' : ''; ?>"
                                                                    <?php echo isset($bh['time'][$days[$i]]['closed']) ? '' : 'required'; ?>>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 col-4">
                                                            <div class="form-group">
                                                                <input type="text"
                                                                    name="time[{{ $days[$i] }}][close]"
                                                                    id="time[{{ $days[$i] }}][close]"
                                                                    value="<?php echo isset($bh['time'][$days[$i]]['close']) ? $bh['time'][$days[$i]]['close'] : ''; ?>"
                                                                    class="etimepicker form-control <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'disabled-input-field' : ''; ?>"
                                                                    <?php echo isset($bh['time'][$days[$i]]['closed']) ? '' : 'required'; ?>>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 col-1">
                                                            <div class="form-group">
                                                                &emsp;
                                                                <input type="checkbox"
                                                                    name="time[{{ $days[$i] }}][closed]"
                                                                    onclick="closed('<?php echo $days[$i]; ?>')"
                                                                    <?php echo isset($bh['time'][$days[$i]]['closed']) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Day & Time Sect End -->
                                                    <?php
                                            }
                                            ?>

                                                    <div class="col-md-12 text-center">
                                                        <button
                                                            class="pl-5 pr-5 pt-2 pb-2 border-0 cstm-edit-btn rounded-pill"
                                                            type="submit">
                                                            Update
                                                        </button>
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

    <style>
        .cstm-edit-btn {
            background: #ffcf42;
            color: black;
            font-weight: 600
        }

        .cstm-edit-btn:hover {
            background: #ffcf42 !important;
        }

        .border {
            border: 1px solid red;
        }
    </style>

</div>
