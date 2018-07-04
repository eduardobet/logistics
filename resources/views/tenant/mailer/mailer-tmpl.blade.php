<?php
$mailer = isset($mailer) ? $mailer : new \Logistics\DB\Tenant\Mailer;
$key = isset($key) ? $key : ':index:';
?>


{!! Form::hidden("mailers[{$key}][eid]", $mailer->id ) !!}

<div class="row det-row">
    <div class="col-lg-5">
        <div class="form-group mg-b-10-force">
            <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
            {!! Form::text("mailers[{$key}][name]", $mailer->name, ['class' => 'form-control form-control-sm', 'required' => '', ]) !!}
        </div>
    </div>

    <div class="col-lg-7">
        <div class="row">
            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Vol Price') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("mailers[{$key}][vol_price]", $mailer->vol_price, ['class' => 'form-control form-control-sm', 'required' => '', ]) !!}
                </div>
            </div>

            <div class="col-4">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Real Price') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("mailers[{$key}][real_price]", $mailer->real_price, ['class' => 'form-control form-control-sm', 'required' => '',  ]) !!}
                </div>
            </div>

            <div class="col-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
                    {!! Form::select("mailers[{$key}][status]", ['A' => __('Active') , 'I' => __('Inactive')  ], $mailer->status, ['class' => 'form-control form-control-sm', 'required' => '', ]) !!}
                </div>
            </div>

            <div class="col-1">
                <div class="form-group mg-t-30-force">
                    <button class="btn btn-sm btn-outline-danger rem-row" type="button" data-id="{{ $mailer->id ? $mailer->id : ':id:' }}" data-del-url="{{ route('tenant.client.extra-contact.destroy', $tenant->domain) }}" data-params='{"id" : "{{$mailer->id}}", "client_id" :"{{$mailer->client_id}}" }'>
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

</div><!-- row -->