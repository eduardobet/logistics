<input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

<div class="row mg-t-10">
</div>

<div class="row">
    <div class="col-lg-12">
        <h4>{{ __('Packages details') }}</h4>
    </div>
</div><!-- row -->

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
<div id="details-container">
    @foreach ($invoice->details as $key => $idetail)
        @include('tenant.warehouse.invoice-detail', ['idetail' => $idetail, 'mode' => 'edit'])
    @endforeach
</div>

<div class="row mg-t-25">

    <div class="col-4">
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
                {!! Form::email('total_volumetric_weight', $invoice->volumetric_weight, ['class' => 'form-control', 'id' => 'total_volumetric_weight', 'readonly' => '', ]) !!}
            </div>
        </div>
    </div>

    <div class="col-4">
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
                {!! Form::email('total_real_weight', $invoice->real_weight, ['class' => 'form-control', 'id' => 'total_real_weight', 'readonly' => '', ]) !!}
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Total to invoice') }}: <span class="tx-danger">*</span></label>
            {!! Form::email('total', $invoice->total, ['class' => 'form-control', 'id' => 'total', 'placeholder' => '000,00$', 'readonly' => '', ]) !!}
        </div>
    </div>
</div>

<div class="row mg-b-20">
    <div class="col-4">
        <strong id="dsp-t-vol"></strong>
    </div>
    <div class="col-4">
        <strong id="dsp-t-real"></strong>
    </div>
    <div class="col-4">
        <strong id="dsp-t-final"></strong>
    </div>
</div>