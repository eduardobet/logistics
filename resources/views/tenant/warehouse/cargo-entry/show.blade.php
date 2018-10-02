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

         <div class="section-wrapper">

            <div class="row row-xs">
                <div class="col-sm-9">
                    <div class="card card-status">
                        <div class="form-group mg-b-0-force">
                            <h4 class="tx-bold tx-inverse">{{ strtoupper(__('Tracking numbers')) }} (#)<span class="tx-danger">*</span></h4>
                        
                            {!! Form::textarea('trackings', $cargo_entry->trackings, ['rows' => 14, 'class' => 'form-control mg-b-6-force', 'readonly' => 1, ]) !!}
                        </div>
                    </div><!-- card -->
                </div><!-- col-3 -->

                <div class="col-sm-3">
                    <div class="card card-status">
                        <h4 class="tx-bold tx-inverse">RECA-{{ $cargo_entry->id }}</h4>
                        <div class="mg-t-0">
                            <p>
                                <b>{{ __('Record details') }}:</b><br>
                                <b>{{ __('Created by') }}:</b> {{ $cargo_entry->creator->full_name  }} <br>
                                <b>{{ __('Date') }}:</b> {{ $cargo_entry->created_at->format('d/m/Y') }}<br>
                                <b>{{ __('Hour') }}:</b> {{ $cargo_entry->created_at->format('g:i A') }}<br>
                                <b>{{ __('Branch') }}:</b> {{ $cargo_entry->branch->name }}<br>
                                <br>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.warehouse.cargo-entry.create', $tenant->domain) }}">
                                    <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
                                </a>
                            </p>
                        </div>
                    </div><!-- card -->
                </div><!-- col-3 -->

            </div>
                
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection