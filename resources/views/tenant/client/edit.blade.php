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

        {!! Form::model($client, ['route' => ['tenant.client.update', $client->id]]) !!}
        
        {{ csrf_field() }}
        {{ method_field('PATCH') }}

        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
        <input type="hidden" name="branch_code" value="{{ $branch->code }}">

         <div id="accordion4" class="accordion-two accordion-two-primary" role="tablist" aria-multiselectable="true">

            
            <div class="card">
              @include('tenant.common._notifications')

              <div class="card-header" role="tab" id="headingOne4">
                <a data-toggle="collapse" data-parent="#accordion4" href="#collapseOne4" aria-expanded="true" aria-controls="collapseOne4" class="tx-gray-800 transition">
                 {{ __('Basic informations') }}
                </a>
              </div><!-- card-header -->

              <div id="collapseOne4" class="collapse show" role="tabpanel" aria-labelledby="headingOne4">
                <div class="card-body">

                  <div class="row">
                    
                    <div class="col-lg-2">
                        <div class="form-group">
                        <label class="form-control-label">#{{ __('Box') }}: </label>
                        <input class="form-control" placeholder="PRXX0000" disabled="" type="text" value="{{ $branch->code }}{{ $client->id }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                        <label class="form-control-label">{{ __('Names') }}: <span class="tx-danger">*</span></label>
                        <input class="form-control" type="text" name="first_name" required value="{{ old('first_name', $client->first_name) }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                        <label class="form-control-label">{{ __('Surnames') }}: <span class="tx-danger">*</span></label>
                        <input class="form-control" type="text" name="last_name" required value="{{ old('last_name', $client->last_name) }}">
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                        <label class="form-control-label">{{ __('PID') }}: <span class="tx-danger">*</span></label>
                        <input class="form-control" type="text" name="pid" required value="{{ old('pid', $client->pid) }}">
                        </div>
                    </div>
                  
                  </div> <!-- row -->

                  <div class="row ">
                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Email') }}: <span class="tx-danger">*</span></label>
                        <input class="form-control" type="email" name="email" placeholder="john.doe@server.com" required value="{{ old('email', $client->email) }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Telephones') }}: <span class="tx-danger">*</span></label>
                        <input class="form-control" type="text" name="telephones" required value="{{ old('telephones', $client->telephones) }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{__('Client type')}}: <span class="tx-danger">*</span></label>
                        
                        {!! Form::select('type', ['C' => __('Common'), 'V' => __('Vendor'), 'E' =>__('Enterprise') ], null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Enterprise name') }}:</label>
                        <input class="form-control" type="text" name="org_name" value="{{ old('org_name', $client->org_name) }}">
                        </div>
                    </div>


                  </div> <!-- row -->

                  <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                            <label class="form-control-label">{{ __('Country') }}:</label>
                                <select name="country_id" class="form-control select2 select2-hidden-accessible" data-placeholder="{{ __('Search') }}" tabindex="-1" aria-hidden="true">
                                <option value="0" label="---"></option>
                                <option value="1">Panam&aacute;</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                            <label class="form-control-label">{{ __('Department') }}:</label>
                                <select name="department_id" class="form-control select2 select2-hidden-accessible" data-placeholder="{{ __('Search') }}" tabindex="-1" aria-hidden="true">
                                <option value="0" label="---"></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                            <label class="form-control-label">{{ __('City') }}:</label>
                                <select name="city_id" class="form-control select2 select2-hidden-accessible" data-placeholder="{{ __('Search') }}" tabindex="-1" aria-hidden="true">
                                <option value="0" label="---"></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Address') }}:</label>
                        <input class="form-control" type="text" name="address" value="{{ old('address', $client->address) }}">
                        </div>
                    </div>

                  </div> <!-- row -->

                  <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="{{ __('Agregar detalles extras de cliente') }}">{{ old('notes', $client->notes) }}</textarea>
                        </div>
                    </div>
                  </div> <!-- row -->

                  <div class="row">
                    <div class="col-lg-2">
                        <label class="ckbox">
                        {!! Form::checkbox('pay_volume', $client->pay_volume, null, []) !!}
                        <span>{{ __('Pay by volume') }}</span>
                        </label>
                    </div>

                    <div class="col-lg-2">
                        <label class="ckbox">
                        {!! Form::checkbox('special_rate', $client->pay_volume, null, []) !!}
                        <span>{{ __('Special rate') }}</span>
                        </label>
                    </div>

                    <div class="col-lg-2">
                        <label class="ckbox">
                        {!! Form::checkbox('special_maritime', $client->special_maritime, null, []) !!}
                        <span>{{ __('Special maritime') }}</span>
                        </label>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group mg-b-10-force">
                            <label class="form-control-label">{{__('Status')}}: <span class="tx-danger">*</span></label>
                            {!! Form::select('status', ['A' => __('Active'), 'I' => __('Inactive')], null, ['class' => 'form-control']) !!}
                         </div>
                    </div>
                  </div> <!-- row -->



                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header" role="tab" id="headingTwo4">
                <a class="collapsed tx-gray-800 transition" data-toggle="collapse" data-parent="#accordion4" href="#collapseTwo4" aria-expanded="false" aria-controls="collapseTwo4">
                  {{ __('Additional contacts') }}
                </a>
              </div>
              <div id="collapseTwo4" class="collapse" role="tabpanel" aria-labelledby="headingTwo4">
                <div class="card-body">
                  
                </div>
              </div>
            </div>

            
          </div> <!-- accordion4 -->





            <div class="row mg-t-25 justify-content-between">
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-primary bg-royal bd-1 bd-gray-400">{{ __('Save') }}</button>
                </div>
            </div>
      </form>

     </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection