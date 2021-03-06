
@include('tenant.common._notifications')

<input type="hidden" name="branch_id" id="branch_id" value="{{ $branch->id }}">

<input type="hidden" name="gen_invoice" id="gen_invoice" value="1">

<div class="row">

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('ID') }}:</label>
             @if (config("app.migrations.{$tenant->id}.warehouses", false) && $mode == 'create')
                {!! Form::number("manual_id", null , ['class' => 'form-control', 'required' => 1,  ]) !!}
             @else
                {!! Form::text('id', isset($warehouse) ? $warehouse->manual_id_dsp : null, ['class' => 'form-control', 'disabled' => 1, ]) !!}
            @endif
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">
                {{ __('Date') }}:
                <span class="tx-danger">*</span>
            </label>

            {!! Form::text('created_at', !$warehouse->created_at ? old('created_at', date('Y-m-d')) : $warehouse->created_at->format('Y-m-d'), ['class' => 'form-control fc-datepicker hasDatepicker', 'readonly' => 1, ]) !!}
            
        </div>
     </div>

    <div class="col-lg-2">
        <div class="form-group">
        
             <label class="form-control-label">{{ __('Type') }}: 
                <span class="tx-danger">*</span>  
             </label>
             {!! Form::select('type', ['' => '----']+['A' => __('Air'), 'M' => __('Maritime'), ], null, ['class' => 'form-control', 'id' => 'type', 'required' => 'required', ]) !!}

        </div>
    </div>

    <div class="col-lg-{{ $mode=='create' ? 6 : 4 }}">
        <div class="form-group">
            <label class="form-control-label">{{ __('Issuer branch') }}: <span class="tx-danger">*</span></label>

            <select name="branch_from" id="branch_from" class="form-control" required>
            <option value="">---</option>
            @foreach ($userBranches as $uBranch)
                <option value="{{ $uBranch->id }}" data-code="{{ $uBranch->code }}" data-dcomission="{{ $uBranch->direct_comission }}" data-should-invoice="{{ $uBranch->should_invoice }}" {{ (isset($warehouse) && $warehouse->branch_from == $uBranch->id) || (old('branch_from') == $uBranch->id ) ? " selected": null }}>
                    {{ $uBranch->name }}
                </option>
            @endforeach
            </select>
        </div>
    </div>

    @if ($mode=='edit' || $mode=='show')
    <div class="col-2">
        <div class="form-group mg-t-30-force">
            <div class="btn-group btn-group-justified">
                <a  title="{{ __('Sticker') }}" href="{{ route('tenant.warehouse.print-sticker', [$tenant->domain, $warehouse->id ]) }}" class="btn btn-outline-dark" role="button">
                    <i class="fa fa-ticket"></i>
                </a>

                <a title="{{ __('Receipt') }}" href="{{ route('tenant.warehouse.receipt', [$tenant->domain, $warehouse->id]) }}" class="btn btn-outline-dark" role="button">
                    <i class="fa fa-file-text-o"></i>
                </a>

                @can('create-invoice')
                    @if ($invoice->total) 
                        @if (config('app.invoice_print_version') == 2)
                            <a  title="{{ __('Print :what', ['what' => __('Invoice') ]) }}" href="{{ route('tenant.invoice.show', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch_id, 'client_id' => $warehouse->client_id, '__printing' => 1, ]) }}" class="btn btn-outline-dark" role="button">
                                <i class="fa fa-print"></i>
                            </a>
                        @else 
                            <a  href="{{ route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id, 'html' => '1', ]) }}" class="btn btn-outline-dark" role="button" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                                <i class="fa fa-file-text-o"></i>
                            </a>
                        @endif
                    @endif
                @endcan
            </div>
        </div>
    </div>
    @endif

</div><!-- row -->

<div class="row">
    <div class="col-lg-12">
        <h4>{{ __('Warehouse details') }}</h4>
    </div>
</div><!-- row -->

<div class="row">

    <div class="col-lg-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Mailer') }}: <span class="tx-danger"></span></label>

            <select name="mailer_id" id="mailer_id" class="form-control">
            <option value="">---</option>
            @foreach ($mailers as $mailer)
                <option value="{{ $mailer->id }}" data-vol_price="{{ $mailer->vol_price }}" data-real_price="{{ $mailer->real_price }}" {{ (isset($warehouse) && $warehouse->mailer_id == $mailer->id) || old('mailer_id') == $mailer->id ? " selected": null }}>
                    {{ $mailer->name }}
                </option>
            @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">

            <label class="form-control-label">{{ __('Destination branch') }}: <span class="tx-danger">*</span></label>

            <select name="branch_to" id="branch_to" class="form-control select2ize" required data-apiurl="{{ route('tenant.api.clients', [':parentId:']) }}" data-child="#client_id">
            <option value="">---</option>
            @foreach ($branches as $aBranch)
                <option value="{{ $aBranch->id }}" {{ (isset($warehouse) && $warehouse->branch_to == $aBranch->id) || old('branch_to') == $aBranch->id ? " selected": null }}
                    data-vol_price={{ $aBranch->vol_price }} data-real_price={{ $aBranch->real_price }} data-dhl_price={{ $aBranch->dhl_price }}
                    data-maritime_price={{ $aBranch->maritime_price }} data-extra_maritime_price={{ $aBranch->extra_maritime_price }} 
                    >
                    {{ $aBranch->name }}
                </option>
            @endforeach
            </select>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
        
             <label class="form-control-label">{{ __('Client') }}: 
                <span class="tx-danger">*</span>
                <strong id="loader-client_id"></strong>
             </label>
             @if (isset($clients))
                <select name="client_id" id="client_id" class="form-control select2" style="width: 100%">
                    <option value="">----</option>
                    @foreach ($clients as $client)
                        <option value='{{ $client->id }}'
                            data-pay_volume='{{ var_export($client->pay_volume) }}' data-special_rate='{{ var_export($client->special_rate) }}' data-special_maritime='{{ var_export($client->special_maritime) }}' data-pay_first_lbs_price='{{ var_export($client->pay_first_lbs_price) }}'
                            data-vol_price='{{ $client->vol_price }}'  data-real_price='{{ $client->real_price }}' data-first_lbs_price='{{ $client->first_lbs_price }}'
                            data-pay_extra_maritime_price='{{ var_export($client->pay_extra_maritime_price) }}'
                            data-extra_maritime_price='{{ var_export($client->extra_maritime_price) }}'
                            data-maritime_price='{{ var_export($client->maritime_price) }}'
                            {{ (isset($warehouse) && $warehouse->client_id == $client->id) || old('client_id') == $client->id ? " selected": null }}
                            required
                        >
                        [{{ $client->branch->code }}{{ $client->manual_id_dsp }}] {{ $client->full_name }}
                        </option>
                    @endforeach
                </select>    
             @else    
                {!! Form::select('client_id', ['' => '----'], null, ['class' => 'form-control select2', 'id' => 'client_id', 'width' => '100% !important', 'required' => 1, ]) !!}
             @endif

        </div>
    </div>

</div><!-- row -->

@if ($mode == 'edit' || $mode == 'show')
<div class="row hidden" id="btn-invoice-container" style="display:none">
    <div class="col-lg-12">
        <button class="btn btn-info btn-sm" type="button" id="btn-invoice" 
        data-url="{{ route('tenant.warehouse.invoice-tpl', $tenant->domain) }}"
        data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
        >
            {{ __('Invoice') }}
        </button>
    </div>
</div><!-- row -->
@endif

<div id="invoice-container" {{$mode == 'create' ? ' style=display:none': null }}>
    @includeWhen(($mode == 'edit' || $mode == 'show'), 'tenant.warehouse.invoice', [
        'invoice' => $invoice,
        'payment' => $payment,
        'mode' => $mode,
    ])
</div><!-- row -->

<div class="row mg-t-10">
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Tracking numbers') }} (<strong id="qty-dsp">{{$warehouse->qty?$warehouse->qty:0}}</strong>): <span class="tx-danger"></span></label>
            {!! Form::textarea('trackings', null, ['class' => 'form-control', 'rows' => 4, 'id' => 'trackings' , ]) !!}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Referencias / Detalles Extras') }}: <span class="tx-danger"></span> </label>
            {!! Form::textarea('reference', null, ['class' => 'form-control', 'rows' => 4, 'id' => 'reference', ]) !!}
        </div>
    </div>
</div><!-- row -->

<div class="row mg-t-10">
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Total packages') }}: <span class="tx-danger">*</span></label>
            {!! Form::number('tot_packages', null, ['class' => 'form-control', 'required' => 1, 'id' => 'tot_packages' ,'step' => ".01", 'min' => "0" ]) !!}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Total weight') }}: <span class="tx-danger">*</span></label>
            {!! Form::number('tot_weight', null, ['class' => 'form-control', 'required' => 1, 'id' => 'reference', 'step' => ".01", 'min' => "0"]) !!}
        </div>
    </div>

</div><!-- row -->

<div class="row mg-t-10">
    <div class="col-lg-3">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Amount paid') }}:</label>
            {!! Form::number("amount_paid", $payment ? $payment->amount_paid : 0, ['class' => 'form-control ', 'id' => 'amount_paid', 'step' => ".01", 'min' => "0"  ] ) !!}
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Payment method') }}:</label>
            {!! Form::select('payment_method', ['' => '----', 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], $payment ? $payment->payment_method : null, ['class' => 'form-control', 'id' => 'payment_method' ] ) !!}
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

</div><!-- row -->

@if ($mode=='edit' || $mode=='show')
    
<div class="row {{ $mode=='edit' ? null : ' d-none' }}" id="invoice-notes">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Notes') }}:  <span class="tx-danger"></span></label>
            {!! Form::textarea('notes', $invoice->notes, ['class' => 'form-control', 'rows' => 4, 'id' => 'notes',  ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Delivered trackings') }}:  <span class="tx-danger"></span></label>
            {!! Form::textarea('delivered_trackings', $invoice->delivered_trackings, ['class' => 'form-control', 'rows' => 4, 'id' => 'delivered_trackings', 'readonly' => 1, ]) !!}
        </div>
    </div>
</div>
@endif

<div class="row mg-t-25 justify-content-between">
    <div class="col">
        @if (!$invoice->is_paid && $warehouse->status != 'I' && !$user->isClient())
            <button id="btn-wh-save" type="submit" class="btn btn-primary  bd-1 bd-gray-400">{{ __('Save') }}</button>
        @endif
        @if (($user->isSuperAdmin() || $user->isAdmin()) && $warehouse->status)
            @if ($warehouse->status == 'A')
                <button id="btn-wh-status-toggle" type="button" data-status="I" class="btn btn-danger  bd-1 bd-gray-400">{{ __('Inactivate') }}</button>
            @else 
                <button id="btn-wh-status-toggle" type="button" data-status="A" class="btn btn-success  bd-1 bd-gray-400">{{ __('Activate') }}</button>  
            @endif
        @endif
    </div>
</div>