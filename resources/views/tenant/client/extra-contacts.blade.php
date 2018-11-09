<?php
$econtact = isset($econtact) ? $econtact : new \Logistics\DB\Tenant\ExtraContact;
$key = isset($key) ? $key : ':index:';
?>


{!! Form::hidden("econtacts[{$key}][eid]", $econtact->id ) !!}


<div class="row det-row">
    <div class="col-lg-3">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Full Name') }}</label>
            {!! Form::text("econtacts[{$key}][efull_name]", $econtact->full_name, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>
    
    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('PID') }}</label>
            {!! Form::text("econtacts[{$key}][epid]", $econtact->pid, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>
    
    <div class="col-lg-2">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Telephones') }}</label>
            {!! Form::text("econtacts[{$key}][etelephones]", $econtact->telephones, ['class' => 'form-control form-control-sm', ]) !!}
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="row">
            <div class="col-10">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Email') }}:</label>
                    {!! Form::email("econtacts[{$key}][eemail]", $econtact->email, ['class' => 'form-control form-control-sm', ]) !!}
                </div>
            </div>
            
            <div class="col-2">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger rem-row" type="button" data-id="{{ $econtact->id ? $econtact->id : ':id:' }}" data-del-url="{{ route('tenant.client.extra-contact.destroy', $tenant->domain) }}" data-params='{"id" : "{{$econtact->id}}", "client_id" :"{{$econtact->client_id}}" }'
                    {{ isset($mode) && $mode == 'show' ? ' disabled' : null }}
                    >
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
              
</div>
<!-- row -->