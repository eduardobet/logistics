@include('tenant.common._notifications')

<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="#informations" data-toggle="tab">{{ __('Informations') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#extra-contacts" data-toggle="tab">{{ __('Extra contacts') }}</a>
    </li>
</ul>

 <div class="tab-content">

    <div class="tab-pane active" id="informations">

        <div class="row mg-t-25">

            <div class="col-lg-2">
                @if ($tenant->migration_mode && $mode == 'create')
                    <label class="form-control-label">#{{ __('Box') }}: <span class="tx-danger">*</span></label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">{{ $branch->code }}</span>
                        </div>
                        {!! Form::number("manual_id", null , ['class' => 'form-control', 'required' => 1,  ]) !!}
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-control-label">#{{ __('Box') }}: </label>
                        {!! Form::text("box", $client->branch ? $client->branch->code . '' . $client->manual_id : null , ['placeholder' => 'PRXX0000', 'class' => 'form-control', 'disabled' => '' ]) !!}
                    </div>
                @endif
            </div>
            

            <div class="col-lg-4">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Names') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("first_name", null, ['class' => 'form-control', 'required' => '' ]) !!}
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    <label class="form-control-label">{{ __('Surnames') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("last_name", null, ['class' => 'form-control', 'required' => '' ]) !!}
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group">
                    <label class="form-control-label">{{ __('PID') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("pid", null, ['class' => 'form-control', 'required' => '' ]) !!}
                </div>
            </div>
            
        </div> <!-- row -->

        <div class="row ">

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Email') }}: <span class="tx-danger">*</span></label>
                    {!! Form::email("email", isset($mode) && $mode === 'create' && $tenant->email_allowed_dup ? $tenant->email_allowed_dup : null, ['class' => 'form-control', 'required' => '', 'placeholder' => 'john.doe@server.com' ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
                    {!! Form::text("telephones", null, ['class' => 'form-control', 'required' => '' ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{__('Client type')}}: <span class="tx-danger">*</span></label>
                    {!! Form::select('type',['' => '----'] + ['C' => __('Common') , 'V' => __('Vendor') , 'E' => __('Enterprise') ], null, ['class' => 'form-control toggle-text', 'required' => '', 'data-target' => '#org_name', 'data-toogle-when' => 'E', 'data-required' => 'Y', 'data-tmp-value' => $client->org_name ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Enterprise name') }}:</label>
                    {!! Form::text("org_name", null, ['class' => 'form-control', $client->org_name ? null : 'readonly' => '', 'id' => 'org_name' ]) !!}
                </div>
            </div>

        </div> <!-- row -->

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Country') }}:</label>
                    {!! Form::select('country_id', ['0' => '----'] + $countries->toArray(), null, ['class' => 'form-control select2ize', 'data-apiurl' => route('tenant.api.department', ['parentId' => ':parentId:']), 'data-child' => '#department_id', 'id' => 'country_id', ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Department') }}: 
                        <strong id="loader-department_id"></strong>    
                    </label>
                    {!! Form::select('department_id', ['0' => '----'] + $departments, null, ['class' => 'form-control select2 select2ize', 'data-apiurl' => route('tenant.api.zone', [':parentId:']), 'data-child' => '#city_id', 'id' => 'department_id', 'width' => '100% !important', ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('City') }}:
                        <strong id="loader-city_id"></strong> 
                    </label>
                    {!! Form::select('city_id', ['0' => '----'] + $zones, null, ['class' => 'form-control select2', 'id' => 'city_id', 'width' => '100% !important', ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Address') }}:</label>
                    {!! Form::text("address", null, ['class' => 'form-control', ]) !!}
                </div>
            </div>

        </div> <!-- row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group mg-b-10-force">
                    <label class="form-control-label">{{ __('Notes') }}</label>
                    {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Details of the client') , 'rows' => 3,]) !!}
                </div>
            </div>
        </div> <!-- row -->

        <div class="row mg-t-20">
            <div class="col-lg-3">
                <div class="input-group">
                    <?php
                        $volPrice = null; $checkit = null;
                        if (isset($mode) && $mode==='create') {$volPrice = $branch->vol_price; $checkit = true;}
                        //else  if (isset($mode) && $mode==='edit' && !$client->vol_price ) {$volPrice = $client->branch->vol_price; $checkit = true;}
                        else {$volPrice = $client->vol_price; $checkit = $client->pay_volume;}
                    ?>

                    <label class="ckbox">
                        {!! Form::checkbox('pay_volume', null, $checkit, ['id' => 'pay_volume', 'data-volprice' => $volPrice  ]) !!} <span>
                            {{ __('Pay by volume') }}
                        </span>
                    </label>
                    
                    &nbsp;
                    {!! Form::text('vol_price', $volPrice , ['id' => 'vol_price', 'class' => 'form-control form-control-sm', 'placeholder' => __('Vol Price'), ]) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="input-group">
                    <label class="ckbox">
                        {!! Form::checkbox('special_rate', null, null, ['id' => 'special_rate', ]) !!} <span>{{ __('Special rate') }}</span>
                    </label>

                    &nbsp;
                    {!! Form::text('real_price', null, ['class' => 'form-control form-control-sm', 'placeholder' => __('Real Price'), ]) !!}
                </div>
            </div>

            <?php
                $flbsPrice = null; $checkit = null;
                if (isset($mode) && $mode==='create') {$flbsPrice = $branch->first_lbs_price; $checkit = true;}
                else {$flbsPrice = $client->first_lbs_price; $checkit = $client->pay_first_lbs_price;}
            ?>
            <div class="col-lg-4">
                <div class="input-group">
                    <label class="ckbox">
                        {!! Form::checkbox('pay_first_lbs_price', null, $checkit, ['id' => 'pay_first_lbs_price', 'data-firstlbsprice' => $flbsPrice,  ]) !!} <span>{{ __('First LBS Price') }}</span>
                    </label>

                    &nbsp;
                    {!! Form::text('first_lbs_price', $flbsPrice, ['class' => 'form-control form-control-sm', 'placeholder' => __('First LBS Price'), 'id' => 'first_lbs_price', ]) !!}
                </div>
            </div>

            <div class="col-lg-2">
                <label class="ckbox">
                    {!! Form::checkbox('special_maritime', null, null, ['id' => 'special_maritime',]) !!} <span>{{ __('Special maritime') }}</span>
                </label>
            </div>

        </div> <!-- row -->

        <div class="row mg-t-20">
            <div class="col-lg-12">
                <div class="form-group">
                    <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
                    {!! Form::select('status', ['A' => __('Active') , 'I' => __('Inactive')  ], null, ['class' => 'form-control form-control-sm', 'required' => '', ]) !!}
                </div>
            </div>
        </div> <!-- row -->


    </div><!-- tab informations -->

    <div class="tab-pane" id="extra-contacts">
        
        @if (!isset($hide_more))
        <div class="mg-t-25">
            <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
            data-url="{{ route('tenant.client.contact-tmpl', $tenant->domain) }}"
            data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
            >
                <i class="fa fa-plus"></i> {{ __('Add') }}
            </button>
        </div>
        @endif

        <div id="details-container">
            <div class="mg-t-25"></div>
            @foreach ($client->extraContacts as $key => $econtact)
                @include('tenant.client.extra-contacts', ['econtact' => $econtact, 'mode' => $mode, ])
            @endforeach
        </div>

    </div><!-- tab extra-contacts -->

</div><!-- tab-content -->

@if ($mode != 'show')
<div class="row mg-t-25 justify-content-between">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary  bd-1 bd-gray-400" id="btn-save-client">{{ __('Save') }}</button>
    </div>
</div>
@endif