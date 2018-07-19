<input type="hidden" name="branch_id" id="branch_id" value="{{ $branch->id }}">

<div class="row">
    <div class="col-lg-12">
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
                            data-pay_volume='{{ $client->pay_volume }}' data-special_rate='{{ $client->special_rate }}' data-special_maritime='{{ $client->special_maritime }}'
                            data-vol_price='{{ $client->vol_price }}'  data-real_price='{{ $client->real_price }}'
                            {{ (isset($warehouse) && $warehouse->client_id == $client->id) || old('client_id') == $client->id ? " selected": null }}
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
    @endforeach
</div>