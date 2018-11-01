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

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            {!! Form::open(['route' => ['tenant.invoice.store', $tenant->domain]]) !!}
                @include('tenant.invoice._fields', [
                    'invoice' => new \Logistics\DB\Tenant\Invoice,
                    'mode' => 'create',
                    'payment' => new \Logistics\DB\Tenant\Payment,
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

        // client side calculation
        $(document).on("blur", ".inline-calc", function() {
            doCalc()
        });

        $(document).on("click", ".rem-row", function() {
            doCalc();
        });

        $("#amount_paid").blur(function() {
            doCalc();
        });
    });

    function doCalc() {
        var $els = $(".inline-calc:not('.removed')", document);
        var total = 0;
        var amountPaid = $("#amount_paid").val() || 0;
        $els.each(function(i, el) {
            var $el = $(el);
            var index = $el.data('i');
            var qty = $("#qty-"+i).not('.removed').val() || 0;
            var _total = $.trim($("#total-"+i).not('.removed').val()) || 0;

            total += _total * qty;
        });

        $("#total").val(total);
        $("#pending").val(total - amountPaid);

        $els = null;
    }
</script>
@stop
