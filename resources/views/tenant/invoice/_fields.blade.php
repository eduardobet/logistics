@if (isset($mode) && $mode == 'edit')
    <div class="row mg-b-20">
        <div class="col">
            <a href="{{ route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id ]) }}" class="btn btn-sm btn-outline-dark" role="button" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                <i class="fa fa-print"></i>
            </a>

            &nbsp;&nbsp;

            <button id="create-payment" type="button" class="btn btn-sm btn-outline-dark"
                data-url="{{ route('tenant.payment.create', [$tenant->domain, $invoice->id, ]) }}"
                title="{{ __('New payment') }}" data-invoice-id="{{ $invoice->id }}"
                data-toggle="modal"
                data-target="#modal-payment"
                {{ $invoice->total == $payments->sum('amount_paid') ? ' disabled' : null }}
            >
                <i class="fa fa-money"></i></a>
            </button>

            &nbsp;&nbsp;

            <button id="resend-invoice" type="button" class="btn btn-sm btn-outline-dark"
                data-url="{{ route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id, ]) }}"
                data-toggle="tooltip" data-placement="left" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $invoice->id }}"
                data-loading-text="<i class='fa fa-spinner fa-spin '></i>"
            >
                <i class="fa fa-envelope"></i></a>
            </button>

        </div>
    </div>
@endif

<input type="hidden" name="branch_id" id="branch_id" value="{{ $branch->id }}">

<div class="row">

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('ID') }}:</label>
            {!! Form::text("id", null, ['class' => 'form-control ', 'readonly' => 1, ]) !!}
        </div>
    </div>

    <div class="col-lg-10">
        <div class="form-group">
        
             <label class="form-control-label">{{ __('Client') }}: 
                <span class="tx-danger">*</span>
                <strong id="loader-client_id"></strong>    
             </label>
             @if (isset($clients))
                <select name="client_id" id="client_id" class="form-control select2" style="width: 100%" required>
                    <option value="">----</option>
                    @foreach ($clients as $client)
                        <option value='{{ $client->id }}'
                            data-pay_volume='{{ $client->pay_volume }}' data-special_rate='{{ $client->special_rate }}' data-special_maritime='{{ $client->special_maritime }}'
                            data-vol_price='{{ $client->vol_price }}'  data-real_price='{{ $client->real_price }}'
                            {{ (isset($invoice) && $invoice->client_id == $client->id) || old('client_id') == $client->id ? " selected": null }}
                        >
                        [{{ $client->boxes->first()->branch_code }}{{ $client->id }}] {{ $client->full_name }}
                        </option>
                    @endforeach
                </select>    
             @else    
                {!! Form::select('client_id', ['' => '----'], null, ['class' => 'form-control select2', 'id' => 'client_id', 'width' => '100% !important', ]) !!}
             @endif

        </div>
     </div>
</div><!-- row -->

<div class="row">
    <div class="col-lg-12">
        <h4>{{ __('Items') }}</h4>
    </div>
</div><!-- row -->

<div class="mg-t-20">
    <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
    data-url="{{ route('tenant.invoice.invoice-detail-tmpl', $tenant->domain) }}"
    data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
    {{ isset($mode) && $mode == 'edit' ? ' disabled' : null }}
    >
        <i class="fa fa-plus"></i> {{ __('Add') }}
    </button>
</div>

<div class="mg-t-25"></div>
<div id="details-container">
    @foreach ($invoice->details as $key => $idetail)
        
        @include('tenant.invoice.detail', [
            'detail' => $idetail
        ])
        
    @endforeach
</div>
<div class="mg-t-25"></div>

<div class="row">

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Total') }}:</label>
            {!! Form::text("total", null, ['class' => 'form-control ', 'readonly' => 1, 'id' => 'total' ]) !!}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Amount paid') }}:</label>
            {!! Form::text("amount_paid", $payment ? $payment->amount_paid : 0, ['class' => 'form-control ', 'id' => 'amount_paid',  ]) !!}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Payment method') }}:</label>
            {!! Form::select('payment_method', ['' => '----', 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], $payment ? $payment->payment_method : null, ['class' => 'form-control', 'id' => 'payment_method' ]) !!}
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Reference') }}:</label>
            {!! Form::text("payment_ref", $payment ? $payment->payment_ref : null, ['class' => 'form-control ', 'id' => 'payment_ref', 'maxlength' => 255,  ]) !!}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Pending') }}:</label>
            {!! Form::text("pending", $invoice->total - ($payment ? $payment->amount_paid : 0), ['class' => 'form-control ', 'id' => 'pending', 'readonly' => '1', ]) !!}
        </div>
    </div>

</div>

<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button id="btn-wh-save" type="submit" class="btn btn-primary bg-royal bd-1 bd-gray-400"
        {{ isset($payments) && $invoice->total == $payments->sum('amount_paid') ? ' disabled' : null }}
        >{{ __('Save') }}</button>
    </div>
</div>