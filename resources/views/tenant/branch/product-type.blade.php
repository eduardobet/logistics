<?php
$product_type = isset($product_type) ? $product_type : new \Logistics\DB\Tenant\ProductType;
$key = isset($key) ? $key : ':index:';
?>

{!! Form::hidden("product_types[{$key}][rid]", $product_type->id ) !!}

<div class="row mg-t-10 det-row">
    
    <div class="col-8">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
            {!! Form::text("product_types[{$key}][name]", $product_type->name, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>  
    
    <div class="col-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Status') }}: <span class="tx-danger">*</span></label>
            {!! Form::select("product_types[{$key}][status]", ['A' => __('Active') , 'I' => __('Inactive') ], $product_type->status, ['class' => 'form-control form-control-sm', 'required' => true]) !!}
        </div>
    </div>

</div>
<!-- row -->