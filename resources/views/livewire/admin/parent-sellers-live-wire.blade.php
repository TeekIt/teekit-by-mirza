<div class="container-xxl flex-grow-1 container-p-y">
    @if (session()->has('error'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0 show" role="alert"
            aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Error</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('error') }}
            </div>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0 show" role="alert"
            aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Success</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
    {{-- ************************************ Info Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="infoModel" tabindex="-1" aria-labelledby="infoModelLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModelLabel">Seller Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <tbody class="table-border-bottom-0">
                                    <tr>
                                        <th>Seller Name</th>
                                        <td>{{ $name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td style="white-space: pre-line;"><?php echo wordwrap($address_1, $width = 50, $break = "\n", $cut = false); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Businee Name</th>
                                        <td>{{ $business_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lat</th>
                                        <td>{{ $lat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lon</th>
                                        <td>{{ $lon }}</td>
                                    </tr>
                                    <tr>
                                        <th>Logo</th>
                                        <td>
                                            <img src=@if ($user_img) "{{ config('constants.BUCKET') . $user_img }}"
                                            @else
                                            "{{ asset('images/icons/store_logo.png') }}" @endif
                                                width="150px">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Login</th>
                                        <td>{{ $last_login }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email Verification Date</th>
                                        <td>{{ $email_verified_at }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pending Withdraw</th>
                                        <td>{{ $pending_withdraw }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Withdraw</th>
                                        <td>{{ $total_withdraw }}</td>
                                    </tr>
                                    <tr>
                                        <th>Online Status</th>
                                        <td>{{ $is_online }}</td>
                                    </tr>
                                    <tr>
                                        <th>Application Fee</th>
                                        <td>{{ $application_fee }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-6 col-md-5 col-xl-6">
            <h4 class="py-4 my-1">Parent Sellers</h4>
        </div>
        <div class="col-12 col-sm-6 col-md-5 col-xl-5">
            <div class="input-group py-4 my-2">
                <input type="text" wire:model.debounce.500ms="search" class="form-control py-3"
                    placeholder="Search here...">
                {{-- <button class="btn btn-primary" type="button"><i class='bx bx-search-alt'></i></button> --}}
            </div>
        </div>
        <div class="col-12 col-md-2 col-xl-1">
            <button type="button" class="btn btn-danger my-3 py-3 w-100" title="Delete selected data" onclick="delUsers()">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
    <div class="container">
        <div class="row">
            @forelse ($data as $single_index)
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-4">
                    <div class="card custom-card text-white custom-card-has-bg"
                        @if ($single_index->user_img) style="background-image:url('{{ config('constants.BUCKET') . $single_index->user_img }}');"
                        @else
                        style="background-image:url('{{ asset('images/icons/store_logo.png') }}');" @endif>
                        <div class="card-img-overlay custom-card-img-overlay d-flex flex-column">
                            <div class="card-body custom-card-body">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input select-checkbox" title="Select"
                                        id="{{ $single_index->id }}">
                                    <button type="button" class="btn btn-primary" title="Show detail information"
                                        data-bs-toggle="modal" data-bs-target="#infoModel"
                                        wire:click="renderInfoModal({{ $single_index->id }})">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <a href="{{ route('admin.orders', ['store_id' => $single_index->id]) }}"
                                        class="btn btn-dark" title="Show orders">
                                        <i class="fas fa-luggage-cart"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer custom-card-footer">
                                <div class="form-check form-switch mt-3">
                                    <input type="checkbox" class="form-check-input" value="{{ $single_index->id }}"
                                        wire:click="changeStatus({{ $single_index->id }}, {{ $single_index->is_active }})"
                                        role="switch" {{ $single_index->is_active === 1 ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        @if ($single_index->is_active === 1)
                                            <b class="bg-success rounded px-2">Active</b>
                                        @else
                                            <b class="bg-danger rounded px-2">Blocked</b>
                                        @endif
                                    </label>
                                </div>
                                <div class="media">
                                    {{-- <img class="mr-3 rounded-circle" src="{{ asset('storage/images/dummyemp.png') }}"
                                        alt="Generic placeholder image" style="max-width:50px"> --}}
                                    <div class="media-body">
                                        <h6 class="my-0 text-white d-block">{{ $single_index->business_name }}</h6>
                                        <small>{{ $single_index->email }}</small> <br>
                                        <small><i class="far fa-clock"></i> Joining:
                                            {{ $single_index->created_at }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <h1 class="text-dark">No Sellers Found :(</h1>
            @endforelse
        </div>
        <div class="row">
            {{ $data->links() }}
        </div>
    </div>

</div>
