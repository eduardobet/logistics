
@include('tenant.common._notifications')

<div class="row">

    <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-5">
        <div class="form-group">
            <label class="form-control-label">{{ __('Address') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('address', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('Code') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('code', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('Initial') }}: <span class="tx-danger">*</span></label>
            {{ Form::text('initial', null, ['class' => 'form-control', 'required' => 'required']) }}
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
            <label class="form-control-label">{{ __('Emails') }}: <span class="tx-danger">*</span></label>
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
    <div class="col-lg-9">
        <div class="form-group">
            <label class="form-control-label">{{ __('Color') }}: <span class="tx-danger">*</span></label>
            <select name="color" id="color" class="form-control select2" style="width: 100%">
                <option value="">---</option>
                @foreach ($colors as $color)
                    <option value="{{ $color->class_name }}"{{ $color->class_name == old('color', $branchData->color) ? " selected" : null }}>
                       <span class="{{$color->class_name}}"></span> {{ $color->translation->name }}
                    </option>
                @endforeach
            </select>
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
    <div class="col-8">
        <div class="form-group">
            <label class="form-control-label">{{ __('Logo') }}:</label>
            <div class="custom-file">
                <input type="file" name="logo" class="custom-file-input" id="logo" lang="{{ config('app.locale') }}" accept="image/png, image/jpeg">
                <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
            </div>
        </div>
    </div>

    <div class="col-4 mg-t-30-force">
        <div class="form-group">
            @if ($branchData->logo)
                <button type="button" class="btn btn-info btn-sm btn-view-image">
                    <i class="fa fa-eye"></i>
                </button>
            @endif
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

<div class="row mg-t-10">

    <div class="col-lg-6">
        <div class="form-group">
            <label class="ckbox">
                {!! Form::checkbox('direct_comission') !!} <span>{{ __('Direct comission') }}</span>
            </label>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            <label class="ckbox">
                {!! Form::checkbox('should_invoice') !!} <span>{{ __('Activate warehouse invoice') }}</span>
            </label>
        </div>
    </div>
    
</div>

<div class="row mg-t-10">
    <div class="col">
        <div class="form-group">
            <label class="form-control-label">{{ __('Vol Price') }}:</label>
            {{ Form::text('vol_price', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label class="form-control-label">{{ __('Real Price') }}:</label>
            {{ Form::text('real_price', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label class="form-control-label">{{ __('DHL') }}:</label>
            {{ Form::text('dhl_price', null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label class="form-control-label">{{ __('S/Marit') }}:</label>
            {{ Form::text('maritime_price', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary  bd-1 bd-gray-400">{{ __('Save') }}</button>
    </div>
</div>