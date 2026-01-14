{{-- 
    How you can call this component example:
    <x-custom-sweet-alert-modal
            :alertIconHTML="'<i class=\'fas fa-exclamation-circle text-warning\'></i>'" :alertHeading="'Alert!'"
            :msg="'Are you sure you want to cancel this requested delivery?'" :confirmButtonText="'Yes'"
            :cancelButtonText="'No'" 
            :confirmButtonFunction="'cancelDelivery(' . $requestedDeliveryId . ')'"
            :cancelButtonFunction="'closeModal(\'customSweetAlertModal\')'"
        /> 
--}}
<div wire:ignore.self class="modal fade" id="customSweetAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body d-flex justify-content-between align-items-center">
                <div class="text-center p-4">
                    <div style="font-size: 80px;">
                        {!! $alertIconHTML !!}
                    </div>
                    <h2 class="text-dark mb-3">{{ $alertHeading }}</h2>
                    <p class="mb-4 fs-5">{{ $msg }}</p>
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-site-primary py-2 px-4"
                            wire:click="{{ $confirmButtonFunction }}" wire:target="{{ $confirmButtonFunction }}"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target={{ $confirmButtonFunction }}>
                                {{ $confirmButtonText }}
                            </span>
                            <span wire:loading wire:target={{ $confirmButtonFunction }}>
                                <span class="spinner-border spinner-border-sm text-light" role="status"
                                    aria-hidden="true"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-secondary py-2 px-4"
                            wire:click="{{ $cancelButtonFunction }}">
                            {{ $cancelButtonText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
