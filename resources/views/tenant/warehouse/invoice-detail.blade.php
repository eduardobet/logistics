<?php
$idetail = isset($idetail) ? $idetail : new \Logistics\DB\Tenant\InvoiceDetail;
$key = isset($key) ? $key : ':index:';
?>

<div class="row det-row" data-i="{{$key}}">
    
    <div class="col-lg-2">
        <div class="row">
            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Qty') }}:</label>
                    {!! Form::number("invoice_detail[{$key}][qty]", $idetail->qty ? $idetail->qty : 1, ['class' => 'form-control form-control-sm qty inline-calc', 'data-i' => "{$key}", 'id' => "qty-{$key}", ]) !!}
                </div>
            </div>
    
            <div class="col-8">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Type') }}:</label>
                    {!! Form::select("invoice_detail[{$key}][type]",[1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', 7=>'Servicio aéreo' ], $idetail->type, ['class' => 'form-control form-control-sm type', 'id' => "type-{$key}" ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="row">
            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Length') }}:</label>
                    {!! Form::number("invoice_detail[{$key}][length]", $idetail->length ?: 0, ['class' => 'form-control form-control-sm length inline-calc', 'data-i' => "{$key}", 'id' => "length-{$key}", 'step' => ".01", 'min' => "0", ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Width') }}:</label>
                    {!! Form::number("invoice_detail[{$key}][width]", $idetail->width ?: 0, ['class' => 'form-control form-control-sm  inline-calc width', 'data-i' => "{$key}", 'id' => "width-{$key}", 'step' => ".01", 'min' => "0", ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Height') }}:</label>
                    {!! Form::number("invoice_detail[{$key}][height]", $idetail->height ?: 0, ['class' => 'form-control form-control-sm inline-calc height', 'data-i' => "{$key}", 'id' => "height-{$key}", 'step' => ".01", 'min' => "0", ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="row">

            <div class="col-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Vol') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][vol_weight]", $idetail->vol_weight, ['class' => 'form-control form-control-sm inline-calc volumetric_weight', 'readonly' => '', 'data-i' => "{$key}", 'id' => "volumetric_weight-{$key}", ]) !!}
                </div>
            </div>

            <div class="col-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Real') }}:</label>
                    {!! Form::number("invoice_detail[{$key}][real_weight]", $idetail->real_weight, ['class' => 'form-control form-control-sm inline-calc real_weight', 'data-i' => "{$key}", 'id' => "real_weight-{$key}", 'step' => ".01", 'min' => "0", ]) !!}
                </div>
            </div>
            
            <div class="col-2">
                <p></p>
                <p></p>
                <p></p>
                <div class="form-check">
                    {!! Form::checkbox("invoice_detail[{$key}][is_dhll]", null, $idetail->is_dhll==true, ['class' => 'form-check-input is_dhll', 'data-i' => "{$key}", 'id' => "is_dhll-{$key}", ]) !!}
                    <label class="form-check-label" for="is_dhll-{{ $key }}">DHL?</label>
                </div>
            </div>

            <div class="col-2">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('# Track.') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][tracking]", $idetail->tracking, ['class' => 'form-control form-control-sm tracking', 'data-i' => "{$key}", 'id' => "tracking-{$key}", ]) !!}
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
                <input type="hidden" name="invoice_detail[{{ $key }}][wdid]" value="{{ $idetail->id }}" id="wdid-{{ $key }}">
                <input type="hidden" name="invoice_detail[{{ $key }}][real_price]" value="{{ $idetail->real_price }}" id="real_price-{{ $key }}">
                <input type="hidden" name="invoice_detail[{{ $key }}][vol_price]" value="{{ $idetail->vol_price }}" id="vol_price-{{ $key }}">
                <input type="hidden" name="invoice_detail[{{ $key }}][total]" value="{{ $idetail->total }}" id="total-{{ $key }}">
            </div>

        </div>
    </div>
              
</div>
<!-- row -->