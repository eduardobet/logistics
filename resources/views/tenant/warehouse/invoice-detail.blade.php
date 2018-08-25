<?php
$idetail = isset($idetail) ? $idetail : new \Logistics\DB\Tenant\InvoiceDetail;
$key = isset($key) ? $key : ':index:';
?>

<div class="row det-row">
    
    <div class="col-lg-4">
        <div class="row">
            <div class="col-2">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Qty') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][qty]", $idetail->qty ? $idetail->qty : 1, ['class' => 'form-control form-control-sm qty inline-calc', 'data-i' => "{$key}", 'id' => "qty-{$key}", ]) !!}
                </div>
            </div>
    
            <div class="col-10">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Type') }}:</label>
                    {!! Form::select("invoice_detail[{$key}][type]",[1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', ], $idetail->type, ['class' => 'form-control form-control-sm type', ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="row">
            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Length') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][length]", $idetail->length, ['class' => 'form-control form-control-sm length inline-calc', 'data-i' => "{$key}", 'id' => "length-{$key}", ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Width') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][width]", $idetail->width, ['class' => 'form-control form-control-sm  inline-calc width', 'data-i' => "{$key}", 'id' => "width-{$key}", ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Height') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][height]", $idetail->height, ['class' => 'form-control form-control-sm inline-calc height', 'data-i' => "{$key}", 'id' => "height-{$key}", ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="row">

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Vol') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][vol_weight]", $idetail->vol_weight, ['class' => 'form-control form-control-sm inline-calc volumetric_weight', 'readonly' => '', 'data-i' => "{$key}", 'id' => "volumetric_weight-{$key}", ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Real') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][real_weight]", $idetail->real_weight, ['class' => 'form-control form-control-sm inline-calc real_weight', 'data-i' => "{$key}", 'id' => "real_weight-{$key}", ]) !!}
                </div>
            </div>

            <div class="col-2">
                    <p></p>
                    <p></p>
                    <p></p>
                <div class="form-check">
                    {!! Form::checkbox("invoice_detail[{$key}][is_dhll]", '2.25', $idetail->is_dhll==true, ['class' => 'form-check-input is_dhll', 'data-i' => "{$key}", 'id' => "is_dhll-{$key}", ]) !!}
                    <label class="form-check-label" for="is_dhll-{{ $key }}">DHL?</label>
                </div>
            </div>
            
            <div class="col-2">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger rem-row" type="button" data-id="{{ $idetail->id ? $idetail->id : ':id:' }}" data-del-url="{{ route('tenant.client.extra-contact.destroy', $tenant->domain) }}" data-params='{"id" : "{{$idetail->id}}", "client_id" :"{{$idetail->client_id}}" }'
                       {{ isset($mode) && $mode == 'edit' ? ' disabled' : null }} 
                    >
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="invoice_detail[{{ $key }}][wdid]" value="{{ $idetail->id }}">
            </div>

        </div>
    </div>
              
</div>
<!-- row -->