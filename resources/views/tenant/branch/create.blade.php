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

         <div  class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            {!! Form::open(['route' => ['tenant.admin.branch.store', $tenant->domain], 'files' => true,]) !!}
                @include('tenant.branch._fields')
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
        @include('common._add_more', ['identifier' => "br-0"]))
        $(function() {
            $("#color").select2()
        })
    </script>
@endsection