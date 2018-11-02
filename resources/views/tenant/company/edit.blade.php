@extends('layouts.tenant')

@section('title')
  {{ __('Dashboard') }}  {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])

<div class="slim-mainpanel">

    <div class="container">

        <div class="slim-pageheader">
            {{ Breadcrumbs::render() }}
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>
         </div><!-- slim-pageheader -->

         @include('tenant.common._notifications')

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active tab-toggle" href="#informations" data-container="informations" data-toggle="tab">{{ __('Informations') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link tab-toggle" href="#remote-addresses" data-container="remote-addresses" data-toggle="tab">{{ __('Remote addresses') }}</a>
                </li>
                </li>
                <li class="nav-item">
                    <a class="nav-link tab-toggle" href="#conditions" data-container="conditions" data-toggle="tab">{{ __('Conditions') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link tab-toggle" href="#email-config" data-container="email-config" data-toggle="tab">{{ __('Email configurations') }}</a>
                </li>
            </ul>

            {!! Form::model($company, ['route' => ['tenant.admin.company.update', $tenant->domain], 'method' => 'PATCH', 'files' => true, ]) !!}
            <div class="tab-content">

                
                <div class="tab-pane active" id="informations">
        
                    <div class="row mg-t-25">

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Name') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('telephones', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Emails') }}: <span class="tx-danger">*</span></label>
                                {{ Form::email('emails', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                    </div><!-- row -->

                    <div class="row">

                        <div class="col-lg-10">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Address') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('address', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Language') }}: <span class="tx-danger">*</span></label>
                                {!! Form::select('lang', ['' => '----']+['es' => 'Español', 'en' => 'English'], null, ['class' => 'form-control', 'required' => true]) !!}
                            </div>
                        </div>

                    </div> <!-- row -->

                    <div class="row">

                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('RUC') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('ruc', null, ['class' => 'form-control', 'required' => 'required', ]) }}
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('DV') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('dv', null, ['class' => 'form-control', 'required' => 'required', ]) }}
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Timezone') }}: <span class="tx-danger"></span></label>
                                {{ Form::text('timezone', null, ['class' => 'form-control', ]) }}
                            </div>
                        </div>

                    </div><!-- row -->

                    <div class="row">
                        <div class="col-8">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Logo') }}:</label>
                                <div class="custom-file">
                                    <input type="file" name="logo" class="custom-file-input" id="logo" lang="{{ config('app.locale') }}">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-4 mg-t-30-force">
                            <div class="form-group">
                                @if ($tenant->logo)
                                    <button type="button" class="btn btn-info btn-sm btn-view-image">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Domain') }}:</label>
                                {{ Form::text('domain-disp', $company->domain, ['class' => 'form-control', 'readonly' => '1', ]) }}
                            </div>
                        </div>
                    </div><!-- row -->

                </div> <!-- tab informations --> 

                <div class="tab-pane" id="remote-addresses">
                    <div class="mg-t-25">
                        <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
                        data-url="{{ route('tenant.compnay.remote-addr-tmpl', $tenant->domain) }}"
                        data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
                        >
                            <i class="fa fa-plus"></i> {{ __('Add') }}
                        </button>
                    </div>

                    <div id="details-container-remote-addresses">
                        <div class="mg-t-25"></div>
                        @foreach ($tenant->remoteAddresses as $key => $raddress)
                            @include('tenant.company.remote-addresses', ['raddress' => $raddress])
                        @endforeach
                    </div>
                
                </div> <!-- tab remote addresses -->

                <div class="tab-pane" id="conditions">
                    <div class="mg-t-25">
                        <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
                        data-url="{{ route('tenant.compnay.condition-tmpl', $tenant->domain) }}"
                        data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
                        >
                            <i class="fa fa-plus"></i> {{ __('Add') }}
                        </button>
                    </div>

                    <div id="details-container-conditions">
                        <div class="mg-t-25"></div>
                        @foreach ($tenant->conditions as $key => $condition)
                            @include('tenant.company.conditions', ['condition' => $condition])
                        @endforeach
                    </div>
                
                </div> <!-- tab conditions -->

                <div class="tab-pane" id="email-config">
                    <div class="mg-t-25"></div>
                    <div id="details-container-email-config">
                        @include('tenant.company.email-config')
                    </div>
                
                </div> <!-- tab email configurations -->

            </div> <!-- tab-content --> 


            <div class="row mg-t-25 justify-content-between">
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-primary  bd-1 bd-gray-400">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
            
                
         </div> <!-- section-wrapper -->
         
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    @include('common._add_more')
    <script>
        $(function() {
            $(".btn-view-image").click(function() {
                swal({
                    imageUrl: '{{ asset("storage/".$tenant->logo) }}',
                })
            });

            //
            $("select[name='mail_driver']").change(function() {
                if (this.value === 'mailgun') {
                    $("#mailgun_domain, #mailgun_secret").prop({disabled: false, required: true});
                } else {
                    $("#mailgun_domain, #mailgun_secret").val('').prop({disabled: true, required: false});
                }
            });

        });
    </script>
@endsection