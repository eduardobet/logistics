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
            {!! Form::model($branchData, ['route' => ['tenant.admin.branch.update', $tenant->domain], 'method' => 'PATCH']) !!}
            {!! Form::hidden('id', $branchData->id) !!}
                @include('tenant.branch._fields')
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection