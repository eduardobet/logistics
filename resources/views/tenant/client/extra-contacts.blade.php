<div class="row mg-t-25 det-row">
    <div class="col-lg-3">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Full Name') }}</label>
            {!! Form::text("econtacts[:index:][full_name]", null, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('PID') }}</label>
            {!! Form::text("econtacts[:index:][pid]", null, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Telephones') }}</label>
            {!! Form::text("econtacts[:index:][telephones]", null, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>

    <div class="col-lg-5">
        <div class="row">
            <div class="col-10">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Email') }}:</label>
                    {!! Form::email("econtacts[:index:][email]", null, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
            
            <div class="col-2">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger" type="button"><i class="fa fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>
              
</div>
<!-- row -->