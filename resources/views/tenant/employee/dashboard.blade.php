@extends('layouts.tenant')

@section('title')
  {{ __('Dashboard') }}  {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])

<div class="slim-mainpanel">

    <div class="container">

        <div class="slim-pageheader">
            {{ Breadcrumbs::render()->toHtml() ? Breadcrumbs::render() : 'Dashboard' }}
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>
         </div><!-- slim-pageheader -->

         <div class="pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            
            @include('tenant.employee.dashboard._totals')
            @include('tenant.employee.dashboard._last_activities_and_profit')
            
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection







