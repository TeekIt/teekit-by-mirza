<!-- Google Map Modal - Begins -->
<div class="modal hide" id="map_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add Location</h4>
                <button type="button" id="locationModel" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="p-3">
                    <div class="col-md-12 my-2">
                        <label for="modal_address">Address 1</label>
                        <input type="text" class="form-control" name="modal_address" id="modal_address"/>
                    </div>
                    <div class="col-md-12 my-2">
                        <label for="modal_unit_address">Address 2 (optional)</label>
                        <input type="text" class="form-control" placeholder="Apartment, unit, suite, or floor#" name="modal_unit_address" id="modal_unit_address">
                    </div>
                    <div class="col-md-12 my-2">
                        <label for="modal_postcode">Postcode</label>
                        <input type="text" class="form-control" name="modal_postcode" id="modal_postcode" />
                    </div>
                    <div class="col-md-12 my-2">
                        <label for="modal_country">Country</label>
                        <input tyep="text" class="form-control" name="modal_country" id="modal_country" />
                    </div>
                    <div class="col-md-12 my-2">
                        <label for="modal_state">State/Province</label>
                        <input tyep="text" class="form-control" name="modal_state" id="modal_state" />
                    </div>
                    <div class="col-md-12 my-2">
                        <label for="modal_city">City</label>
                        <input type="text" class="form-control" name="modal_city" id="modal_city" />
                    </div>
                    {{-- Google Maps --}}
                    <div class="col-md-12 my-2">
                        <div style="min-height: 300px;" id="map-canvas"></div>
                    </div>
                    <div class="row my-2">
                        <div class="col-md-6">
                            <label for="modal_lat">Lat</label>
                            <input type="text" name="modal_lat" id="modal_lat" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label for="modal_long">Long</label>
                            <input type="text" name="modal_long" id="modal_long" class="form-control" />
                        </div>
                    </div>
                    <button type="submit" onclick="submitLocation()" class="d-no mt-3 btn btn-submit btn-block btn-outline-primary">
                        Submit
                    </button>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Google Map Modal - Ends -->