@if (json_decode(Auth::user()->business_hours)->submitted == null)
    {{-- ************************************ Set Store Hours Modal ************************************ --}}
    <div class="modal fade custom-z-index" id="businessHoursModal" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Set Store Hours</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <form action="{{ route('seller.update.required.info') }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{-- <div class="pb-5">
                                <label>Stripe Account Id</label>
                                <input type="text" name="stripe_account_id" class="form-control" placeholder="Enter your stripe account id" required>
                            </div> --}}
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
                            @php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            @endphp
                            @for ($i = 0; $i < count($days); $i++)
                                <!-- Day & Time Sect Begins -->
                                <div class="row form-inline">
                                    <div class="col-md-2 col-3">
                                        <div class="form-group">
                                            <p class="day">{{ $days[$i] }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="form-group">
                                            <input type="text" name="time[{{ $days[$i] }}][open]" id="time[{{ $days[$i] }}][open]" class="stimepicker form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="form-group">
                                            <input type="text" name="time[{{ $days[$i] }}][close]" id="time[{{ $days[$i] }}][close]" class="etimepicker form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-1">
                                        <div class="form-group">
                                            &emsp;
                                            <input type="checkbox" name="time[{{ $days[$i] }}][closed]" onclick="closed('<?php echo $days[$i]; ?>')">
                                        </div>
                                    </div>
                                </div>
                                <!-- Day & Time Sect Ends -->
                            @endfor
                            <div class="col-md-12 text-center">
                                <button style="background: #ffcf42;color:black;font-weight: 600;" class="px-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <style>
        .custom-z-index {
            z-index: 1100 !important;
        }

        .ui-timepicker-container {
            z-index: 1600 !important;
        }
    </style>
@endif
