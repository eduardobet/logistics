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
                    {!! Form::text("invoice_detail[{$key}][qty]", $idetail->qty, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
    
            <div class="col-10">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Type') }}:</label>
                    {!! Form::select("invoice_detail[{$key}][epid]",[1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', ], $idetail->type, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="row">
            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Length') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][length]", $idetail->length, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Width') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][width]", $idetail->width, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Height') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][height]", $idetail->height, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="row">

            <div class="col-5">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Vol') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][volumetric_weight]", $idetail->volumetric_weight, ['class' => 'form-control form-control-sm', 'readonly' => '' ]) !!}
                </div>
            </div>

            <div class="col-5">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('P/Real') }}:</label>
                    {!! Form::text("invoice_detail[{$key}][real_weight]", $idetail->real_weight, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
            
            <div class="col-2">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger rem-row" type="button" data-id="{{ $idetail->id ? $idetail->id : ':id:' }}" data-del-url="{{ route('tenant.client.extra-contact.destroy', $tenant->domain) }}" data-params='{"id" : "{{$idetail->id}}", "client_id" :"{{$idetail->client_id}}" }'>
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
              
</div>
<!-- row -->