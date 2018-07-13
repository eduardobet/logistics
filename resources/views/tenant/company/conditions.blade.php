<?php
$condition = isset($condition) ? $condition : new \Logistics\DB\Tenant\Condition;
$key = isset($key) ? $key : ':index:';
?>


{!! Form::hidden("conditions[{$key}][cid]", $condition->id ) !!}


<div class="row mg-t-10 det-row">
    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Type') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("conditions[{$key}][ctype]", ['' => '----']+['A' => __('Air'), 'M' => __('Maritime')], $condition->type, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Address') }}</label>
            {!! Form::text("conditions[{$key}][address]", $condition->address, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>  
    
    <div class="col-lg-4">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Telephones') }}</label>
            {!! Form::text("conditions[{$key}][telephones]", $condition->telephones, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div> 

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('Status') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("conditions[{$key}][status]", ['A' => __('Active') , 'I' => __('Inactive') ], $condition->status, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>


</div>
<!-- row -->