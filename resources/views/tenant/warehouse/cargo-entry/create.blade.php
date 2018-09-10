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
            {!! Form::open(['route' => ['tenant.warehouse.cargo-entry.store', $tenant->domain]]) !!}

            @include('tenant.common._notifications')

            <div class="row row-xs">
                <div class="col-sm-12">
                    <div class="card card-status">
                        <div class="form-group mg-b-0-force">
                        <h4 class="tx-bold tx-inverse">{{ strtoupper(__('Tracking numbers')) }} (#)<span class="tx-danger">*</span></h4>
                        
                        {!! Form::textarea('trackings', null, ['rows' => 14, 'class' => 'form-control mg-b-6-force', 'required' => 1, ]) !!}
                        
                        <button class="btn btn-primary" type="submit"><b>{{ __('Save') }}</b></button><br>
                        </div>
                    </div><!-- card -->
                </div><!-- col-sm-12 -->

            </div>
                
                <input type="hidden" id="branch_id" name="branch_id" value="{{ $branch->id }}">
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection