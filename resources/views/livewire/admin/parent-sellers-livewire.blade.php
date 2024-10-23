<div class="container-xxl flex-grow-1 container-p-y">
      
    <x-session-messages />

    {{-- ************************************ Info Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-lg-down" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Seller Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <div class="modal-body">
                    {{-- @if ($modal_error)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            {{ $modal_error_msg }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    @if ($modal_success)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong>
                            {{ $modal_success_msg }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif --}}
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
                                    <td style="white-space: pre-line;"><?php echo wordwrap($full_address, $width = 50, $break = "\n", $cut = false); ?>
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
                                {{-- Commissions Sections - Begins --}}
                                <tr>
                                    <th colspan="2" class="text-center">
                                        Apply Commissions
                                        <p class="text-muted text-sm fw-light">
                                            Apply the agreed commission for this seller on the following categories
                                        </p>
                                    </th>
                                </tr>
                                @if ($categories)
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    wire:click="enableThis('enable_fixed_commission')"
                                                    name="commissions" id="fixedCommissionForAll"
                                                    @if ($enable_fixed_commission) checked @endif>
                                                <label class="form-check-label fw-bold" for="fixedCommissionForAll">
                                                    Fixed For All
                                                </label>
                                            </div>
                                            <div>
                                                <input type="number" class="form-control px-3"
                                                    wire:model.defer="fixed_commission"
                                                    placeholder="Enter fixed commission"
                                                    @if (!$enable_fixed_commission) disabled @endif>
                                                <small class="text-danger">
                                                    @error('fixed_commission')
                                                        {{ $message }}
                                                    @enderror
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    wire:click="enableThis('enable_different_commissions')"
                                                    name="commissions" id="differentCommissionForAll"
                                                    @if ($enable_different_commissions) checked @endif>
                                                <label class="form-check-label fw-bold" for="differentCommissionForAll">
                                                    Different For All
                                                </label>
                                            </div>
                                            @foreach ($categories as $single_index => $value)
                                                <div class="d-flex">
                                                    <div class="p-2 w-50" wire:ignore>{{ $value->category_name }}</div>
                                                    <div class="p-2 w-50">
                                                        <input type="number" class="form-control px-3"
                                                            wire:model.defer="different_commissions.{{ $single_index }}"
                                                            placeholder="Enter commission for this category"
                                                            @if (!$enable_different_commissions) disabled @endif>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <small class="text-danger">
                                                @error('different_commissions')
                                                    {{ $message }}
                                                @enderror
                                            </small>
                                            <div>
                                                <button type="button" class="btn btn-site-primary w-100 mt-4"
                                                    wire:click="applyCommission" wire:loading.class="btn-dark"
                                                    wire:loading.class.remove="btn-site-primary"
                                                    wire:loading.attr="disabled" wire:target="applyCommission"
                                                    @if (!$enable_apply_commission_btn) disabled @endif>
                                                    <span wire:loading.remove wire:target="applyCommission">Apply</span>
                                                    <span wire:loading wire:target="applyCommission">
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Commissions Sections - Ends --}}

                                    {{-- Service Fees Section - Begins --}}
                                    <tr>
                                        <th colspan="2" class="text-center">
                                            Apply Service Fees
                                            <p class="text-muted text-sm fw-light">
                                                You can either apply fixed or different service fees on all categories
                                                in
                                                which the seller is selling his products
                                            </p>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    wire:click="enableThis('enable_fixed_service_fees')"
                                                    name="service_fees" id="fixedServiceFeesForAll"
                                                    @if ($enable_fixed_service_fees) checked @endif>
                                                <label class="form-check-label fw-bold" for="fixedServiceFeesForAll">
                                                    Fixed For All
                                                </label>
                                            </div>
                                            <div>
                                                <input type="number" class="form-control px-3"
                                                    wire:model.defer="fixed_service_fees"
                                                    placeholder="Enter fixed service fees"
                                                    @if (!$enable_fixed_service_fees) disabled @endif>
                                                <small class="text-danger">
                                                    @error('fixed_service_fees')
                                                        {{ $message }}
                                                    @enderror
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    wire:click="enableThis('enable_different_service_fees')"
                                                    name="service_fees" id="differentServiceFeesForAll"
                                                    @if ($enable_different_service_fees) checked @endif>
                                                <label class="form-check-label fw-bold"
                                                    for="differentServiceFeesForAll">
                                                    Different For All
                                                </label>
                                            </div>
                                            @foreach ($categories as $single_index => $value)
                                                <div class="d-flex">
                                                    <div class="p-2 w-50" wire:ignore>{{ $value->category_name }}
                                                    </div>
                                                    <div class="p-2 w-50">
                                                        <input type="number" class="form-control px-3"
                                                            wire:model.defer="different_service_fees.{{ $single_index }}"
                                                            placeholder="Enter service fees for this category"
                                                            @if (!$enable_different_service_fees) disabled @endif>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <small class="text-danger">
                                                @error('different_service_fees')
                                                    {{ $message }}
                                                @enderror
                                            </small>
                                            <div>
                                                <button type="button" class="btn btn-site-primary w-100 mt-4"
                                                    wire:click="applyServiceFees" wire:loading.class="btn-dark"
                                                    wire:loading.class.remove="btn-site-primary"
                                                    wire:loading.attr="disabled" wire:target="applyServiceFees"
                                                    @if (!$enable_apply_service_fees_btn) disabled @endif>
                                                    <span wire:loading.remove
                                                        wire:target="applyServiceFees">Apply</span>
                                                    <span wire:loading wire:target="applyServiceFees">
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Service Fees Section - Ends --}}
                                @else
                                    <tr>
                                        <td colspan="2">
                                            <div>
                                                <p class="p-2 text-center text-danger">
                                                    This seller has not uploaded products in any category yet
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
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
            <button type="button" class="btn btn-danger my-3 py-3 w-100" title="Delete selected data"
                onclick="delUsers()">
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
                                        data-bs-toggle="modal" data-bs-target="#infoModal"
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

    <style>
        .pl-modal-checkbox {
            padding-left: 35px !important;
        }
    </style>
</div>
