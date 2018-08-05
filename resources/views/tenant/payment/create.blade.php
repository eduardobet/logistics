<div id="modal-payment" class="modal fade" style="display: none;" aria-hidden="true">
     <div class="modal-dialog modal-dialog-vertical-center modal-lg" role="document">
            <div class="modal-content bd-0 tx-14">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">{{ __('New payment') }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="form-payment">
                <input type="hidden" name="p_invoice_id" id="p_invoice_id" value="">
            <div class="modal-body pd-25">
                        <div class="row">

                            <div class="col-lg-3">
                                <div class="form-group mg-b-10-force">
                                    <label class="form-control-label">{{ __('Amount paid') }}:</label>
                                    <?php $pending = isset($invoice) && isset($payments) ? $invoice->total - $payments->sum('amount_paid') : 0;  ?>
                                    {!! Form::number("p_amount_paid", $pending, ['class' => 'form-control ', 'id' => 'p_amount_paid', 'required' => '1', 'step' => "0.01", 'min' => "1", 'max' => $pending, 'onclick' => 'this.select()' ]) !!}
                                </div>
                            </div>
                            
                            <div class="col-lg-3">
                                <div class="form-group mg-b-10-force">
                                    <label class="form-control-label">{{ __('Payment method') }}:</label>
                                    {!! Form::select('p_payment_method', ['' => '----', 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], null, ['class' => 'form-control', 'id' => 'p_payment_method', 'required' => '1',]) !!}
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="form-group mg-b-10-force">
                                    <label class="form-control-label">{{ __('Reference') }}:</label>
                                    {!! Form::text("p_payment_ref", null, ['class' => 'form-control ', 'id' => 'p_payment_ref', 'minlength' => 3, 'maxlength' => 255, 'required' => '1', ]) !!}
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btn-submit-payment" class="btn btn-primary"
                            data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Saving') }}..."
                        >{{ __('Save') }}</button>
                        <button type="button" id="btn-cancel-payment" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </form>
      </div><!-- modal-dialog -->
 </div>