<div id="modal-penalize" class="modal fade" style="display: none;" aria-hidden="true">
     <div class="modal-dialog modal-dialog-vertical-center modal-lg" role="document">
            <div class="modal-content bd-0 tx-14">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">{{ __('Fine :who', ['who' => __('Client')]) }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="form-penalize">
                <div class="modal-body pd-25">
                    <div class="row">

                        <div class="col-lg-3">
                            <div class="form-group mg-b-10-force">
                                <label class="form-control-label">{{ __('Total') }}:</label>
                                <input type="text" name="fine_total" id="fine_total" class="form-control" value="{{ isset($total) ? $total : '10' }}">
                            </div>
                        </div>
                        
                        <div class="col-lg-9">
                            <div class="form-group mg-b-10-force">
                                <label class="form-control-label">{{ __('Reference') }}:</label>
                                <input type="text" name="fine_ref" id="fine_ref" class="form-control" value="{{ isset($ref) ? $ref : __('Fine for leaving packages more than 10 days in warehouse') }}">
                            </div>
                        </div>
                            
                      </div>
                 </div>
                    <div class="modal-footer">
                        <button type="submit" id="btn-submit-penalize" class="btn btn-primary"
                            data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Saving') }}..."
                        >{{ __('Save') }}</button>
                        <button type="button" id="btn-cancel-penalize" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </form>
      </div><!-- modal-dialog -->
 </div>