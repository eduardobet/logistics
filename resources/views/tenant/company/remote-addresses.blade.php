<?php
$raddress = isset($raddress) ? $raddress : new \Logistics\DB\Tenant\RemoteAddress;
$key = isset($key) ? $key : ':index:';
?>


{!! Form::hidden("remote_addresses[{$key}][rid]", $raddress->id ) !!}


<div class="row mg-t-10 det-row">
    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Type') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("remote_addresses[{$key}][type]", ['' => '----']+['A' => __('Air'), 'M' => __('Maritime')], $raddress->type, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Address') }}</label>
            {!! Form::text("remote_addresses[{$key}][address]", $raddress->address, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>  
    
    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Telephones') }}</label>
            {!! Form::text("remote_addresses[{$key}][telephones]", $raddress->telephones, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div> 

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('Status') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("remote_addresses[{$key}][status]", ['A' => __('Active') , 'I' => __('Inactive') ], $raddress->status, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>


</div>
<!-- row -->