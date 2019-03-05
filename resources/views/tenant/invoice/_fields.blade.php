@if (isset($mode) && $mode == 'edit')
    <div class="row mg-b-20">
        <div class="col">
            
            @if (config('app.invoice_print_version') == 2)

                <a target="_blank" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}" href="{{ route('tenant.invoice.show', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch_id, 'client_id' => $invoice->client->id, '__printing' => 1, ]) }}" class="btn btn-sm btn-outline-dark" role="button">
                    <i class="fa fa-print"></i>
                </a>

            @else
                <a target="_blank" href="{{ route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id, 'html' => 1, ]) }}" class="btn btn-sm btn-outline-dark" role="button" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                    <i class="fa fa-print"></i>
                </a>
            @endif

            &nbsp;&nbsp;

            <button id="create-payment" type="button" class="btn btn-sm btn-outline-dark"
                data-url="{{ route('tenant.payment.create', [$tenant->domain, $invoice->id, ]) }}"
                title="{{ __('New payment') }}" data-invoice-id="{{ $invoice->id }}"
                data-toggle="modal"
                data-target="#modal-payment"
                {{ $invoice->status == 'I' || $invoice->total == $payments->sum('amount_paid') ? ' disabled' : null }}
            >
                <i class="fa fa-money"></i></a>
            </button>

            &nbsp;&nbsp;

            <button id="resend-invoice{{ $tenant->email_allowed_dup===$invoice->client->email ? '-nope' : null }}" type="button" class="btn btn-sm btn-outline-dark"
                data-url="{{ route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id, ]) }}"
                data-toggle="tooltip" data-placement="left" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $invoice->id }}"
                data-loading-text="<i class='fa fa-spinner fa-spin '></i>"
                {{ $invoice->status == 'I' ? ' disabled' : null }}
            >
                <i class="fa fa-envelope"></i></a>
            </button>

        </div>
    </div>
@endif

<input type="hidden" name="branch_id" id="branch_id" value="{{ $branch->id }}">

@if (config("app.migrations.{$tenant->id}.internet_invoices", false))
    <div class="row">
        <div class="col-lg-12">
            <h4>
                <label class="badge badge-danger">{{ __('Migration mode') }}...</label>
            </h4>
        </div>
    </div>
@endif

<div class="row">

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('ID') }}:</label>
            @if (config("app.migrations.{$tenant->id}.internet_invoices", false) && $mode == 'create')
                {!! Form::number("manual_id", null , ['class' => 'form-control', 'required' => 1,  ]) !!}
            @else
                {!! Form::text("id_dsp", $invoice->branch ? "{$invoice->branch->initial}-{$invoice->manual_id_dsp}" : null, ['class' => 'form-control ', 'readonly' => 1, ]) !!}
            @endif
            
            {!! Form::hidden('id', null) !!}
            
        </div>
    </div>

    <div class="col-lg-7">
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
                            data-pay_extra_maritime_price='{{ var_export($client->pay_extra_maritime_price) }}'
                            data-extra_maritime_price='{{ var_export($client->extra_maritime_price) }}'
                            data-maritime_price='{{ var_export($client->maritime_price) }}'
                            {{ (isset($invoice) && $invoice->client_id == $client->id) || old('client_id') == $client->id ? " selected": null }}
                        >
                        [{{ $client->branch ? $client->branch->code : null }}{{ $client->manual_id_dsp }}] {{ $client->full_name }}
                        </option>
                    @endforeach
                </select>    
             @else    
                {!! Form::select('client_id', ['' => '----'], null, ['class' => 'form-control select2', 'id' => 'client_id', 'width' => '100% !important', ]) !!}
             @endif

        </div>
     </div>

     <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">
                {{ __('Date') }}:
                <span class="tx-danger">*</span>
            </label>

            {!! Form::text('created_at', !$invoice->created_at ? old('created_at', date('Y-m-d')) : $invoice->created_at->format('Y-m-d'), ['class' => 'form-control fc-datepicker hasDatepicker', 'readonly' => 1, ]) !!}
            
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
            'detail' => $idetail,
            'product_types' => $product_types,
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
            {!! Form::text("amount_paid", $payment ? $payment->amount_paid : 0, ['class' => 'form-control ', 'id' => 'amount_paid',  ]+($payment&&$payment->amount_paid?['readonly' => 1]:[]) ) !!}
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

<div class="row {{ $mode=='edit' ? null : ' d-none' }}" id="invoice-notes">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Notes') }}</label>
            {!! Form::textarea('notes', $invoice->notes, ['class' => 'form-control', 'rows' => 4, ]) !!}
        </div>
    </div>
</div>

<div class="row mg-t-25 justify-content-between">
    <div class="col">
        @if ($invoice->status == 'A')
            <button id="btn-wh-save" type="submit" class="btn btn-primary  bd-1 bd-gray-400"
        {{ $invoice->status == 'I' || (isset($payments) && $invoice->total == $payments->sum('amount_paid')) ? ' disabled' : null }}
        >{{ __('Save') }}</button>
        @endif
        @if (auth()->user()->isSuperAdmin())
            @if ($invoice->status == 'A')
                <button id="btn-inv-status-toggle" type="button" data-status="I" class="btn btn-danger  bd-1 bd-gray-400">{{ __('Inactivate') }}</button>
            @else 
                <button id="btn-inv-status-toggle" type="button" data-status="A" class="btn btn-success  bd-1 bd-gray-400">{{ __('Activate') }}</button>  
            @endif
        @endif
    </div>
</div>