
@include('tenant.common._notifications')

<div class="row">

    <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Address') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('address', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Code') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('code', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
            {!! Form::select('status', ['A' => __('Active'), 'I' => __('Inactive')], null, ['class' => 'form-control']) !!}
        </div>
    </div>

</div>

<div class="row">

    <div class="col-lg-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('telephones', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Email') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('emails', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Fax') }}:</label>
            {{ Form::text('faxes', null, ['class' => 'form-control']) }}
        </div>
    </div>

</div>

<div class="row">

    <div class="col-lg-3 col-sm-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Lat') }}:</label>
            {{ Form::text('lat', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Lng') }}:</label>
            {{ Form::text('lng', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('RUC') }}:</label>
            {{ Form::text('ruc', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('DV') }}:</label>
            {{ Form::text('dv', null, ['class' => 'form-control']) }}
        </div>
    </div>

</div>

<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary bg-royal bd-1 bd-gray-400">{{ __('Save') }}</button>
    </div>
</div>