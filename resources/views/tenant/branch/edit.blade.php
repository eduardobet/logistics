@extends('layouts.tenant')

@section('title')
  {{ __('Dashboard') }} | {{ __('Editing :what', ['what' => __('Branch') ]) }} # {{ request('id') }}
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
            {!! Form::model($branchData, ['route' => ['tenant.admin.branch.update', $tenant->domain], 'method' => 'PATCH','files' => true,]) !!}
            {!! Form::hidden('id', $branchData->id) !!}
                @include('tenant.branch._fields')
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    @include('common._add_more', ['identifier' => "br-{$branchData->id}"]))
    <script>
        $(function() {
            $(".btn-view-image").click(function() {
                swal({
                    imageUrl: '{{ asset("storage/".$branchData->logo) }}',
                })
            });
        });
    </script>
@endsection