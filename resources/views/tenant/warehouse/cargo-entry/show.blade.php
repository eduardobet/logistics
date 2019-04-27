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

         <div class="section---wrapper">

            <div class="row row-xs">
                <div class="col-sm-9">
                    <div class="card card-status">
                        <div class="form-group mg-b-0-force">
                            <h4 class="tx-bold tx-inverse">{{ strtoupper(__('Tracking numbers')) }} ({{ count(explode("\n", $cargo_entry->trackings)) }})<span class="tx-danger">*</span></h4>
                        
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

                                @if ($cargo_entry->client_id && !$user->isClient())
                                <b>{{ __('Created by') }}:</b> <a href="{{ route('tenant.client.show', [$tenant->domain, $cargo_entry->client_id]) }}">
                                    <i class="fa fa-external-link"></i> {{ $cargo_entry->creator->full_name  }}
                                </a><br>
                                @else
                                <b>{{ __('Created by') }}:</b> {{ $cargo_entry->creator->full_name  }} <br>
                                @endif

                                <b>{{ __('Date') }}:</b> {{ $cargo_entry->created_at->format('d/m/Y') }}<br>
                                <b>{{ __('Hour') }}:</b> {{ $cargo_entry->created_at->format('g:i A') }}<br>
                                <b>{{ __('Branch') }}:</b> {{ $cargo_entry->branch->name }}<br>
                                <b>{{ __('Weight') }}:</b> {{ $cargo_entry->weight }} (LBS)<br>

                                @if (!$cargo_entry->type || $cargo_entry->type == 'N' )
                                    <label class="badge badge-success">
                                        {{ __('Normal') }}
                                    </label>
                                @elseif($cargo_entry->type == 'M')
                                    
                                    <label class="badge badge-danger">
                                        {{ __('Misidentified') }}
                                    </label>
                                @endif

                                @can('create-reca')
                                <br>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.warehouse.cargo-entry.create', [$tenant->domain, 'type' => 'N']) }}">
                                    <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
                                </a>

                                <a class="btn btn-sm btn-outline-danger" href="{{ route('tenant.warehouse.cargo-entry.create', [$tenant->domain, 'type' => 'M']) }}">
                                    <i class="fa fa-plus mg-r-5"></i> {{ __('Malidentificada') }}
                                </a>
                                @endcan
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