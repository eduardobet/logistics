
@include('tenant.common._notifications')

<div class="row">

    <div class="col-lg-10">
        <div class="form-group">
            <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
            {!! Form::select('status', ['A' => __('Active'), 'I' => __('Inactive')], null, ['class' => 'form-control']) !!}
        </div>
    </div>

</div>

<div class="row">
    <div class="col-lg-12">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Description') }}</label>
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
        </div>
    </div>
</div> <!-- row -->

<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary  bd-1 bd-gray-400">{{ __('Save') }}</button>
    </div>
</div>