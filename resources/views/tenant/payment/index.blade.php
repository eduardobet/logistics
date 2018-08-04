@extends('layouts.tenant')

@section('title')
  {{ __('Payments') }} | {{ config('app.name', '') }}
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
            
            @include('tenant.payment._index', ['payments' => $payments])
            
            @if ($searching == 'N')
                <div id="result-paginated" class="mg-t-25">
                    {{ $payments->links() }}
                </div>
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