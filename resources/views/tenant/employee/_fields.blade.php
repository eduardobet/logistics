
@include('tenant.common._notifications')

<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="#informations" data-toggle="tab">{{ __('Informations') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#permissions" data-toggle="tab">{{ __('Permissions') }}</a>
    </li>
</ul>

 <div class="tab-content">

    <div class="tab-pane active" id="informations">

        <div class="row mg-t-25">

            <div class="col-lg-3">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Names') }}: <span class="tx-danger">*</span></label>
                    {{ Form::text('first_name', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Surnames') }}: <span class="tx-danger">*</span></label>
                    {{ Form::text('last_name', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Email') }}: <span class="tx-danger">*</span></label>
                    {{ Form::email('email', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    <label class="form-control-label">{{ __('PID') }}: <span class="tx-danger">*</span></label>
                    {{ Form::text('pid', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{__('Position')}}: <span class="tx-danger">*</span></label>
                    {!! Form::select('position', ['' => '----']+$positions->pluck('name', 'id')->toArray() , null, ['class' => 'form-control', 'required' => true]) !!}
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
                    {{ Form::text('telephones', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
                    {!! Form::select('status', $status, null, ['class' => 'form-control']) !!}
                </div>
            </div>

            </div>

            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{__('Type')}}: <span class="tx-danger">*</span></label>
                        {!! Form::select('type', ['' => '----']+['A' => __('Administrator'), 'E' => __('Employee'), ], null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="form-group">
                        <label class="form-control-label">{{ __('Address') }}:</label>
                        {{ Form::text('address', null, ['class' => 'form-control', ]) }}
                    </div>
                </div> 

            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Notes') }}</label>
                        {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                </div>
            </div> <!-- row -->

    </div> <!-- tab informations -->

    <div class="tab-pane" id="permissions">

        <div class="mg-t-25">
        </div>

        @foreach ($permissions->groupBy('header') as $group => $permissions)
            <label class="section-title">{{ $group }}</label>

            <ul class="list-group">
                @foreach ($permissions as $permission)
                    <li class="list-group-item">
                        
                        <label class="ckbox">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->slug }}"
                            {{ in_array($permission->slug, $employee->permissions) ? ' checked' : '' }}
                            >
                            <span>{{ $permission->name }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        @endforeach

        
        
        
        
    </div> <!-- tab permissions -->

    
</div> <!-- tab-content -->


<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary bg-royal bd-1 bd-gray-400">{{ __('Save') }}</button>
    </div>
</div>