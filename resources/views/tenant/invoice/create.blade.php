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
            {!! Form::open(['route' => ['tenant.invoice.store', $tenant->domain]]) !!}
                @include('tenant.invoice._fields', [
                    'invoice' => new \Logistics\DB\Tenant\Invoice,
                    'mode' => 'create',
                ])
                <input type="hidden" id="qty" name="qty" value="">
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection


@section('xtra_scripts')
    @include('common._add_more')
    
    <script>
    var cache = {};
    $(function() {
        $("#client_id").select2({width: 'resolve'});
    });
</script>
@stop
