<?php
$idetail = isset($idetail) ? $idetail : new \Logistics\DB\Tenant\InvoiceDetail;
$key = isset($key) ? $key : ':index:';
?>

<div class="row det-row">
    
    <div class="col-lg-4">
        <div class="row">
            <div class="col-2">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Qty') }}:<span class="tx-danger">*</span></label>
                    {!! Form::text("invoice_detail[{$key}][qty]", $idetail->qty ? $idetail->qty : 1, ['class' => 'form-control form-control-sm qty inline-calc preserve', 'data-i' => "{$key}", 'id' => "qty-{$key}", 'required' => '1', ]) !!}
                </div>
            </div>
    
            <div class="col-10">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Type') }}:<span class="tx-danger">*</span></label>

                    <select id="type-{{$key}}" name="invoice_detail[{{$key}}][type]" class="form-control form-control-sm type preserve" required>
                        <option value="">---</option>
                        @foreach ($product_types as $pt)
                            <option value="{{ $pt->id }}" data-commission="{{ $pt->is_commission ? 5 : null }}" data-commission-desc="{{ $pt->is_commission ? __('Use of credit card') : null }}" data-i="{{$key}}" {{isset($idetail) && $idetail->type ==  $pt->id ? ' selected' : null }}>
                                {{ $pt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Description') }}:<span class="tx-danger">*</span></label>
            {!! Form::text("invoice_detail[{$key}][description]", $idetail->description, ['class' => 'form-control form-control-sm description preserve', 'data-i' => "{$key}", 'id' => "description-{$key}", 'required' => '1', ]) !!}
        </div>
    </div>

    <div class="col-lg-4">
        <div class="row">
            <div class="col-5">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Purchase ID') }}:<span class="tx-danger">*</span></label>
                    {!! Form::text("invoice_detail[{$key}][id_remote_store]", $idetail->id_remote_store ?: 0, ['class' => 'form-control form-control-sm id_remote_store preserve', 'data-i' => "{$key}", 'id' => "id_remote_store-{$key}", 'required' => '1', ]) !!}
                </div>
            </div>

            <div class="col-5">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Total') }}:<span class="tx-danger">*</span></label>
                    {!! Form::text("invoice_detail[{$key}][total]", $idetail->total, ['class' => 'form-control form-control-sm  inline-calc total preserve', 'data-i' => "{$key}", 'id' => "total-{$key}", 'required' => '1', ]) !!}
                </div>
            </div>

            <div class="col-2">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger rem-row" type="button" data-id="{{ $idetail->id ? $idetail->id : ':id:' }}" data-del-url="{{ route('tenant.client.extra-contact.destroy', $tenant->domain) }}" data-params='{"id" : "{{$idetail->id}}", "client_id" :"{{$idetail->client_id}}" }'
                        data-tmp-row-id="{{ $key }}"
                       {{ isset($mode) && $mode == 'edit' ? ' disabled' : null }} 
                    >
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="invoice_detail[{{ $key }}][idid]" value="{{ $idetail->id }}">
            </div>
           
        </div>
    </div>

             
</div>
<!-- row -->