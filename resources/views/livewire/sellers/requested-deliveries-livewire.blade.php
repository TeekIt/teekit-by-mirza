<div>

    @php
        use App\Enums\PackageWeightEnum;
        use App\Enums\PackageTransportTypeEnum;
        use App\Enums\DeliveryProviderEnum;
    @endphp

    <x-session-messages />

    {{-- ************************************ Cancel Requested Delivery Model ************************************ --}}
    {{-- <x-custom-sweet-alert-modal :alertIconHTML="'<i class=\'fas fa-exclamation-circle text-warning\'></i>'" :alertHeading="'Alert!'"
        :msg="'Are you sure you want to cancel this requested delivery?'" :confirmButtonText="'Yes'"
        :cancelButtonText="'No'" :confirmButtonFunction="'cancelDelivery('.$requestedDeliveryId.
        ')'"
        :cancelButtonFunction="'closeModal(\'customSweetAlertModal\')'" /> --}}

    {{-- ************************************ Track Gophr Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="trackGophrDeliveryModal" tabindex="-1">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title display-center">Live Delivery Tracking</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetComponent">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if (isset($selectedDeliveryDetails) && $deliveryServiceName === DeliveryProviderEnum::GOPHR->value)
                        <div class="row" style="height: 100vh;">
                            <iframe src="{{ $selectedDeliveryDetails['data']['deliveries'][0]['public_tracker_url'] }}"
                                class="col-12">
                            </iframe>
                        </div>
                    @endif
                </div>
                <div class="modal-footer hidden">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="resetComponent">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ************************************ Track Stuart Delivery Modal ************************************ --}}
    <div wire:ignore.self class="modal fade" id="trackStuartDeliveryModal" tabindex="-1">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title display-center">Live Delivery Tracking</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetComponent">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if (isset($selectedDeliveryDetails) && $deliveryServiceName === DeliveryProviderEnum::STUART->value)
                        <div class="row" style="height: 100vh;">
                            <iframe src="{{ $selectedDeliveryDetails['deliveries'][0]['tracking_url'] }}"
                                class="col-12">
                            </iframe>
                        </div>
                    @endif
                </div>
                <div class="modal-footer hidden">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="resetComponent">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <div class="container pt-4">
                <form wire:submit.prevent="render">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8 col-sm-12 col-md-8">
                                    <div class="row">
                                        <div class="col-12 col-sm-12">
                                            <input type="date" wire:model.defer="search" class="form-control mb-2">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-12 col-md-4 d-flex flex-sm-row">
                                    <button type="submit" class="btn btn-site-primary p-1 w-100 mx-1"
                                        wire:target="search" wire:loading.class="btn-dark"
                                        wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled"
                                        title="Search">
                                        <span class="fas fa-search" wire:target="search" wire:loading.remove=""></span>
                                        <span wire:target="search" wire:loading="">
                                            <span class="spinner-border spinner-border-sm text-light" role="status"
                                                aria-hidden="true"></span>
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-primary p-1 w-100 mx-1"
                                        wire:click="resetThisPage" wire:target="resetThisPage"
                                        wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary"
                                        wire:loading.attr="disabled" title="Reset this page">
                                        <span class="fas fa-sync" wire:target="resetThisPage"
                                            wire:loading.remove=""></span>
                                        <span wire:target="resetThisPage" wire:loading="">
                                            <span class="spinner-border spinner-border-sm text-light" role="status"
                                                aria-hidden="true"></span>
                                        </span>
                                    </button>
                                    <a type="button" href="{{ route('seller.request.delivery.form') }}"
                                        class="btn btn-primary pt-2 px-1 w-100 mx-1" title="Request New">
                                        <span class="fas fa-plus"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-12 col-sm-12 col-md-12">
                        <h4 class="py-4 my-1 text-site-primary">Requested Deliveries</h4>
                    </div>
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover table-responsive border-bottom">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th scope="col">#</th>
                                    <th scope="col">Pickup Address</th>
                                    <th scope="col">Dropoff Address</th>
                                    <th scope="col">Unit Address</th>
                                    <th scope="col">Buyer Name</th>
                                    <th scope="col">Buyer Contact</th>
                                    <th scope="col">Buyer Email</th>
                                    <th scope="col">Transport Type</th>
                                    <th scope="col">Package Weight</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Delivery Provider</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $singleIndex)
                                    <tr>
                                        <td>{{ $singleIndex->id }}</td>
                                        <td>{{ $singleIndex->pickup_address }}</td>
                                        <td>{{ $singleIndex->dropoff_address }}</td>
                                        <td>{{ $singleIndex->unit_address }}</td>
                                        <td>{{ $singleIndex->receiver_name }}</td>
                                        <td>{{ $singleIndex->receiver_phone }}</td>
                                        <td>{{ $singleIndex->receiver_email }}</td>
                                        <td>{{ $singleIndex->package_transport_type }}</td>
                                        <td>{{ $singleIndex->package_weight }}</td>
                                        <td><span class="badge badge-primary">inProgress</span></td>
                                        <td>{{ $singleIndex->delivery_provider }}</td>
                                        <td>{{ $singleIndex->created_at }}</td>
                                        <td>
                                            {{-- <button type="button"
                                                wire:click="renderTrackDeliveryModal('{{ $singleIndex->delivery_id }}', '{{ $singleIndex->delivery_provider }}')"
                                                class="btn text-site-primary" title="Track delivery">
                                                <i class="far fa-eye"></i>
                                            </button> --}}

                                            <button type="button" class="btn text-site-primary"
                                                wire:click="renderTrackDeliveryModal('{{ $singleIndex->delivery_id }}', '{{ $singleIndex->delivery_provider }}')"
                                                wire:target="renderTrackDeliveryModal('{{ $singleIndex->delivery_id }}', '{{ $singleIndex->delivery_provider }}')"
                                                wire:loading.class="btn-dark" wire:loading.class.remove=""
                                                wire:loading.attr="disabled" title="Track delivery">
                                                <span
                                                    wire:target="renderTrackDeliveryModal('{{ $singleIndex->delivery_id }}', '{{ $singleIndex->delivery_provider }}')"
                                                    wire:loading.remove>
                                                    <i class="far fa-eye"></i>
                                                </span>
                                                <span
                                                    wire:target="renderTrackDeliveryModal('{{ $singleIndex->delivery_id }}', '{{ $singleIndex->delivery_provider }}')"
                                                    wire:loading>
                                                    <span class="spinner-border spinner-border-sm text-light"
                                                        role="status"></span>
                                                </span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <td colspan="12">
                                        <p class="text-dark text-center p-2 fs-3">
                                            {{ config('constants.NO_RECORD') }} 🥺
                                        </p>
                                    </td>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12">
                                {{ $data->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
</div>
