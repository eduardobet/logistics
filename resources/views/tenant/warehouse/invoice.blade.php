<input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

<div class="row mg-t-10">
</div>

<div class="row">
    <div class="col-lg-12">
        <h4>{{ __('Packages details') }}</h4>
    </div>
</div><!-- row -->

@if (config("app.migrations.{$tenant->id}.warehouse_invoices", false) && $mode == 'edit')
    <div class="row mg-t-20">
        <div class="col-lg-12">
            <div class="form-group mg-b-10-force">
                <label class="form-control-label">#{{ __('Invoice') }}: <span class="tx-danger">*</span></label>
                <input type="number" name="manual_id" id="manual_id" value="{{ (int)$invoice->manual_id_dsp ? $invoice->manual_id_dsp : null }}" class="form-control" required>
            </div>
        </div>
    </div>
@endif



<div class="mg-t-25"></div>
<div id="details-container">
    @foreach ($invoice->details as $key => $idetail)
    @include('tenant.warehouse.invoice-detail', ['idetail' => $idetail, 'mode' => 'edit'])
    @endforeach
</div>
<div class="mg-t-25"></div>

@if (!$invoice->total)
    <div class="mg-t-20">
        <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
        data-url="{{ route('tenant.warehouse.invoice-detail-tmpl', $tenant->domain) }}"
        data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
        {{ isset($warehouse) && $warehouse->client_id ? null : 'disabled' }}
        id="btn-add-details"
        >
            <i class="fa fa-plus"></i> {{ __('Add') }}
        </button>
    </div>
@endif
<div class="mg-t-25"></div>

<div class="row mg-t-25">

    <div class="col-lg-5">
        <div class="row">
            <div class="col-6">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Total P/Vol:&nbsp;</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <label class="ckbox wd-16 mg-b-0">
                                <input type="checkbox" id="chk-t-volumetric-weight" name="chk_t_volumetric_weight"{{ $invoice->i_using == 'V' ? ' checked' : null }}><span></span>
                                </label>
                            </div>
                        </div>
                        {!! Form::text('total_volumetric_weight', $invoice->volumetric_weight, ['class' => 'form-control', 'id' => 'total_volumetric_weight', 'readonly' => '', ]) !!}
                        <div class="input-group-append">
                            <div class="input-group-text">
                                 <b>$</b> <strong id="dsp-t-vol"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Total P/Real:&nbsp;</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <label class="ckbox wd-16 mg-b-0">
                                <input type="checkbox" id="chk-t-real-weight" name="chk_t_real_weight"{{ $invoice->i_using == 'R' ? ' checked' : null }}><span></span>
                                </label>
                            </div>
                        </div>
                        {!! Form::text('total_real_weight', $invoice->real_weight, ['class' => 'form-control', 'id' => 'total_real_weight', 'readonly' => '', ]) !!}
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <b>$</b> <strong id="dsp-t-real"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- col-lg-4 -->

    <div class="col-lg-5">
        <div class="row">
            <div class="col-7">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">Total {{ __('Cubic feet') }}:&nbsp;</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <label class="ckbox wd-16 mg-b-0">
                                <input type="checkbox" id="chk-t-cubic-feet" name="chk_t_cubic_feet"{{ $invoice->i_using == 'C' ? ' checked' : null }}><span></span>
                                </label>
                            </div>
                        </div>
                        {!! Form::text('total_cubic_feet', $invoice->cubic_feet, ['class' => 'form-control', 'id' => 'total_cubic_feet', 'readonly' => '', ]) !!}
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <b>$</b> <strong id="dsp-t-cubic"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-5">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Maritime rate') }}: <span class="tx-danger"></span></label>
                    {!! Form::text('maritime_rate', null, ['class' => 'form-control', 'id' => 'maritime_rate', 'readonly' => 1, ]) !!}
                </div>
            </div>
        </div>    
    </div><!-- col-lg-4 -->

    <div class="col-lg-2">
        <div class="row">
            <div class="col">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Total to invoice') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text('total', $invoice->total, ['class' => 'form-control', 'id' => 'total', 'placeholder' => '000,00$', 'readonly' => '', ]) !!}
                </div>
            </div>
        </div>    
    </div><!-- col-lg-4 -->
    
</div>