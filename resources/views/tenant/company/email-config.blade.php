<div class="row">

    <div class="col-4">
        <div class="form-group">
            <label class="form-control-label">{{ __('Driver') }}:</label>
            {!! Form::select('mail_driver', ['' => '----', 'mailgun' => 'Mailgun', 'smtp' => 'SMTP'], null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Host') }}:</label>
            {{ Form::text('mail_host', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

    <div class="col-2">
        <div class="form-group">
            <label class="form-control-label">{{ __('Port') }}:</label>
            {{ Form::text('mail_port', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

</div><!-- row -->