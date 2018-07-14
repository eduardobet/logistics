<?php
$condition = isset($condition) ? $condition : new \Logistics\DB\Tenant\Condition;
$key = isset($key) ? $key : ':index:';
?>


{!! Form::hidden("conditions[{$key}][cid]", $condition->id ) !!}


<div class="row mg-t-10 det-row">
    <div class="col-lg-6">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Type') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("conditions[{$key}][ctype]", ['' => '----']+['I' => __('Invoice'), 'W' => __('Warehouse receipt')], $condition->type, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Status') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("conditions[{$key}][cstatus]", ['A' => __('Active') , 'I' => __('Inactive') ], $condition->status, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>

</div>
<!-- row -->

<div class="row mg-t-10 det-row">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Content') }}: <span class="tx-danger">*</span></label>
            
            {!! Form::textarea("conditions[{$key}][ccontent]", $condition->content, ['class' => 'form-control form-control-sm', 'required' => '', 'rows' => '4', ]) !!}
            
        </div>
    </div>
</div>
<!-- row -->