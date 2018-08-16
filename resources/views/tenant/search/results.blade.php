@extends('layouts.tenant')

@section('title')
  {{ __('Search') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}
           
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>

        </div><!-- slim-pageheader -->

        


        <div class="table-responsive-sm">
            @if (isset($noresults))
                <h3>{{ $noresults }}</h3>
            @endif

            @if (isset($results))
                {{ $results->toJson() }}
            @endif


        </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
    $(function() {
    });
    </script>
@stop