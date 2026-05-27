<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    {{-- ************************************ Info Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-lg-down" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Company Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetComponent"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <tbody class="table-border-bottom-0">
                                <tr>
                                    <th>Company Name</th>
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
                                    <td style="white-space: pre-line;"><?php echo wordwrap($fullAddress ?? '', $width = 50, $break = "\n", $cut = false); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Business Name</th>
                                    <td>{{ $businessName }}</td>
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
                                        <img src=@if ($userImg) "{{ config('constants.BUCKET') . $userImg }}"
                                             @else
                                             "{{ asset('images/icons/store_logo.png') }}" @endif
                                            width="150px">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Login</th>
                                    <td>{{ $lastLogin }}</td>
                                </tr>
                                <tr>
                                    <th>Email Verification Date</th>
                                    <td>{{ $emailVerifiedAt }}</td>
                                </tr>
                                <tr>
                                    <th>Online Status</th>
                                    <td>
                                        @if ($isOnline == 1)
                                            <span class="badge bg-success">online</span>
                                        @else
                                            <span class="badge bg-secondary">offline</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetComponent">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-md-5 col-xl-6">
            <h4 class="py-4 my-1 text-site-primary">Companies</h4>
        </div>
        <div class="col-12 col-sm-6 col-md-5 col-xl-5">
            <div class="input-group py-4 my-2">
                <input type="text" wire:model.live.debounce.500ms="search" class="form-control py-3"
                    placeholder="Search here...">
            </div>
        </div>
        <div class="col-12 col-md-2 col-xl-1">
            <button type="button" class="btn btn-danger my-3 py-3 w-100" title="Delete Selected" onclick="delUsers()">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>

    <div class="container">
        <div class="row">
            @forelse ($data as $singleIndex)
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-4">
                    <div class="card custom-card text-white custom-card-has-bg"
                        @if ($singleIndex->user_img) style="background-image:url('{{ config('constants.BUCKET') . $singleIndex->user_img }}');"
                        @else
                        style="background-image:url('{{ asset('images/icons/store_logo.png') }}');" @endif>
                        <div class="card-img-overlay custom-card-img-overlay d-flex flex-column">
                            <div class="card-body custom-card-body">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input select-checkbox" title="Select"
                                        id="{{ $singleIndex->id }}">
                                    <button type="button" class="btn btn-primary" title="Show detail information"
                                        data-bs-toggle="modal" data-bs-target="#infoModal"
                                        wire:click="renderInfoModal({{ $singleIndex->id }})">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <a href="{{ route('admin.companies.add.sellers', ['companyId' => $singleIndex->id]) }}"
                                        class="btn btn-success" title="Add sellers">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="{{ route('admin.orders', ['store_id' => $singleIndex->id]) }}"
                                        class="btn btn-dark" title="Show orders">
                                        <i class="fas fa-luggage-cart"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer custom-card-footer">
                                <div class="form-check form-switch mt-3">
                                    <input type="checkbox" class="form-check-input" value="{{ $singleIndex->id }}"
                                        wire:click="changeStatus({{ $singleIndex->id }}, {{ $singleIndex->is_active }})"
                                        role="switch" {{ ($singleIndex->is_active === 1) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        @if ($singleIndex->is_active === 1)
                                            <b class="bg-success rounded px-2">Active</b>
                                        @else
                                            <b class="bg-danger rounded px-2">Blocked</b>
                                        @endif
                                    </label>
                                </div>
                                <div class="media">
                                    <div class="media-body">
                                        <h6 class="my-0 text-white d-block">{{ $singleIndex->business_name }}</h6>
                                        <small>{{ $singleIndex->email }}</small> <br>
                                        <small><i class="far fa-clock"></i> Joining:
                                            {{ $singleIndex->created_at }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-dark fs-2 text-center">{{ config('constants.NO_RECORD') }} 🥺</p>
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
