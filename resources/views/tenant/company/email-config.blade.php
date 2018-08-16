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

<div class="row">

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Mailgun domain') }}:</label>
            {{ Form::text('mailgun_domain', null, ['class' => 'form-control', 'id' => 'mailgun_domain', ] + ($company->mail_driver=='mailgun' ? [] : ['disabled' => 1] ) ) }}
        </div>
    </div>

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Mailgun secret') }}:</label>
            {{ Form::text('mailgun_secret', null, ['class' => 'form-control', 'id' => 'mailgun_secret', ] + ($company->mail_driver=='mailgun' ? [] : ['disabled' => 1] )  ) }}
        </div>
    </div>

</div><!-- row -->

<div class="row">

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Username') }}:</label>
            {{ Form::text('mail_username', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

    <div class="col-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Password') }}:</label>
            {{ Form::text('mail_password', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

    <div class="col-3">
        <div class="form-group">
            <label class="form-control-label">{{ __('Encryption') }}:</label>
            {!! Form::select('mail_encryption', ['' => '----', 'tls' => 'TLS',], null, ['class' => 'form-control']) !!}
        </div>
    </div>

</div><!-- row -->

<div class="row">

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Sender email') }}:</label>
            {{ Form::email('mail_from_address', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

    <div class="col-6">
        <div class="form-group">
            <label class="form-control-label">{{ __('Sender name') }}:</label>
            {{ Form::text('mail_from_name', null, ['class' => 'form-control', ]) }}
        </div>
    </div>

</div><!-- row -->