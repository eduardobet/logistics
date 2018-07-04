
@include('tenant.common._notifications')

<div class="row">

    <div class="col-{{$mode=='create' ? 12 : 10}}">
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

    @if ($mode=='edit')
    <div class="col-2">
        <div class="form-group mg-t-30-force">
            <button type="button" class="btn btn-lg"><i class="fa fa-ticket"></i></button>
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
            <label class="form-control-label">{{ __('Mailer') }}: <span class="tx-danger">*</span></label>

            <select name="mailer_id" id="mailer_id" class="form-control" required>
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

            <label class="form-control-label">{{ __('Destination branch') }}: <span class="tx-danger"></span></label>

            <select name="branch_to" id="branch_to" class="form-control">
            <option value="">---</option>
            @foreach ($branches as $aBranch)
                <option value="{{ $aBranch->id }}" {{ (isset($warehouse) && $warehouse->branch_to == $aBranch->id) || old('branch_to') == $aBranch->id ? " selected": null }}>
                    {{ $aBranch->name }}
                </option>
            @endforeach
            </select>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
             <label class="form-control-label">{{ __('Client') }}:</label>
             <select name="client_id" id="client_id" class="form-control" disabled>
                <option value="">---</option>
             </select>
        </div>
    </div>

</div><!-- row -->

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

<div id="invoice-container" {{$mode == 'create' ? ' style=display:none': null }}>
    @includeWhen($mode == 'edit', 'tenant.warehouse.invoice', [
        'invoice' => $invoice,
    ])
</div><!-- row -->

<div class="row mg-t-10">
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Tracking numbers') }} (<strong id="qty-dsp">{{$warehouse->qty?$warehouse->qty:0}}</strong>): <span class="tx-danger">*</span></label>
            {!! Form::textarea('trackings', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 4, 'id' => 'trackings', ($mode=='create'?'readonly':"") => ($mode=='create'?'readonly':"") , ]) !!}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Referencias / Detalles Extras') }}: <span class="tx-danger">*</span> </label>
            {!! Form::textarea('reference', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 4, 'id' => 'reference', ]) !!}
        </div>
    </div>
</div><!-- row -->

<div class="row {{ $mode=='edit' ? null : ' d-none' }}" id="invoice-notes">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Notes') }}</label>
            {!! Form::textarea('notes', $invoice->notes, ['class' => 'form-control', 'rows' => 4, ]) !!}
        </div>
    </div>
</div>

<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button id="btn-wh-save" type="submit" class="btn btn-primary bg-royal bd-1 bd-gray-400">{{ __('Save') }}</button>
    </div>
</div>