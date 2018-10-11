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

            <div class="row">
                <div class="col-12">
                    <h5>
                        {{ __('Invoice') }}
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        @if ($payment->invoice->is_paid)
                            <span class="badge badge-success"><small>{{ __('Paid') }}</small></span>
                        @else
                            <span class="badge badge-danger"><small>{{ __('Pending') }}</small></span>
                        @endif
                    </h5>
                </div>
            </div>

            <div class="row">
                
                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">#:</label>
                        <input type="text" value="{{ $payment->invoice->branch->initial }}-{{ $payment->invoice->id }}" class="form-control" readonly>
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Type') }}:</label>
                        
                        @if ($payment->invoice->warehouse)
                            <input type="text" value="Warehouse" class="form-control" readonly>
                        @else    
                            <input type="text" value="Internet" class="form-control" readonly>
                        @endif
                        
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Total') }}:</label>
                        <input type="text" value="$ {{ number_format($payment->invoice->total, 2) }}" class="form-control" readonly>
                    </div>    
                </div>
                
             </div>

         </div><!--section-wrapper--> 

        <br>
        
        <div class="section-wrapper">       

            <div class="row">
                <div class="col-12">
                    <h5>{{ __('Payment') }} </h5>
                </div>
            </div>

            <div class="row">
            
                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">#:</label>
                        <input type="text" value="{{ $payment->id }}" class="form-control" readonly>
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Payment method') }}:</label>
                        
                        {!! Form::select('payment_method', ['' => '----', 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], $payment->payment_method, ['class' => 'form-control', 'id' => 'payment_method', 'disabled' => '1',]) !!}
                        
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Total') }}:</label>
                        <input type="text" value="$ {{ number_format($payment->amount_paid, 2) }}" class="form-control" readonly>
                    </div>    
                </div>
                
             </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Reference') }}:</label>
                        <input type="text" value="{{ $payment->payment_ref }}" class="form-control" readonly>
                    </div>
                </div>
            </div>
          </div><!--section-wrapper-->

          <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                @if ($payment->creator)
                    <p>{{ __('Created by') }} <b>{{ $payment->creator->full_name }}</b> | <b>{{ $payment->created_at->format('d/m/Y') }}</b> | {{ $payment->created_at->format('g:i A') }} </p>
                @endif    
                @if ($payment->editor)
                    <p>{{ __('Edited by') }} <b>{{ $payment->editor->full_name }}</b> | <b>{{ $payment->updated_at->format('d/m/Y') }}</b> | {{ $payment->updated_at->format('g:i A') }} </p>
                @endif
                
            </div>
          </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection