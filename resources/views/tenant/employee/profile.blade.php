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
                    <a class="nav-link active" href="#basic-info" data-toggle="tab">{{ __('Informations') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#change-password" data-toggle="tab">{{ __('Password') }}</a>
                </li>
            </ul>

            {!! Form::model($employee, ['route' => ['tenant.employee.profile.update', $tenant->domain], 'method' => 'PATCH', 'files' => true, ]) !!}

            <div class="tab-content">

                <div class="tab-pane active" id="basic-info">

                    <div class="row mg-t-20">

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
                                {{ Form::email('email_dsp', $employee->email, ['class' => 'form-control', 'readonly' => '1']) }}
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

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
                                {{ Form::text('telephones', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Address') }}:</label>
                                {{ Form::text('address', null, ['class' => 'form-control', ]) }}
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Avatar') }}:</label>
                                <div class="custom-file">
                                    <input type="file" name="avatar" class="custom-file-input" id="avatar" lang="{{ config('app.locale') }}">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
                                </div>
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

                </div> <!--basic-info--> 

                <div class="tab-pane" id="change-password">
                    
                    <div class="row mg-t-20">

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Current password') }}:</label>
                                {{ Form::password('current_password', ['class' => 'form-control', ]) }}
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('New password') }}:</label>
                                {{ Form::password('new_password', ['class' => 'form-control', ]) }}
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">{{ __('Password confirmation') }}:</label>
                                {{ Form::password('new_password_confirmation', ['class' => 'form-control', ]) }}
                            </div>
                        </div>

                    </div>

                </div> <!--change-password--> 

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