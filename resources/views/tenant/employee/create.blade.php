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
            {!! Form::open(['route' => ['tenant.admin.employee.store', $tenant->domain]]) !!}
            
                @include('tenant.employee._fields', [
                    'status' => ['L' => __('Lock'),]
                ])
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
        var cache = {};
        $(function() {
            $("#in_branch").select2({width: 'resolve'});
        });
    </script>
@stop