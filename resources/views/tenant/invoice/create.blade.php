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
            {!! Form::open(['route' => ['tenant.invoice.store', $tenant->domain], 'id' => 'frm-create', 'name' => 'frm-create']) !!}
                @include('tenant.invoice._fields', [
                    'invoice' => new \Logistics\DB\Tenant\Invoice,
                    'mode' => 'create',
                    'payment' => new \Logistics\DB\Tenant\Payment,
                    'product_types' => $product_types,
                ])
                <input type="hidden" id="qty" name="qty" value="">
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection


@section('xtra_scripts')
    @include('common._add_more', ['identifier' => "inv-0"]))
    
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

        $('.fc-datepicker').datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            language: '{{ config("app.locale") }}',
            format: 'yyyy-mm-dd',
            todayBtn: 'linked'
        });

        //
        $(document).on('change', '.type', function() {
            var self = $(this);
            var i = self.find(':selected').attr('data-i');
            var cValue = self.find(':selected').attr('data-commission');
            var cDesc = self.find(':selected').attr('data-commission-desc');

            if (i && cValue && cDesc) {
                $("#total-"+i).val(cValue).trigger('change');
                $("#description-"+i).val(cDesc).trigger('change');

                doCalc();
            } else {
                $("#total-"+i).val(0).trigger('change');
                $("#description-"+i).val('').trigger('change'); 
                doCalc();   
            }
        });

        // preserving details
        restoreView();

        $("#frm-create").change(function(e) {
            var $el = $(e.target)
            var id = $el.prop('id');
            var i = $el.data('i') || $el.find(':selected').attr('data-i') || $el.closest('.row').find('.qty').data('i');

            console.log("changing......")

            if (tmpRows && $el.hasClass('preserve')) {
                var cRow = JSON.parse(tmpRows['row-'+i])

                var dom = $('<vaina>').append($(cRow));
                dom.find("#"+id).each(function() {
                    var el = $(this);
                    var newEl = el.attr("value", $el.val());

                    if (el.get(0).tagName == 'SELECT') {
                        newEl.find("option:eq("+$el.val()+")").attr("selected", "selected");
                    }

                    el.replaceWith(newEl)
                })

                tmpRows['row-' + i] = JSON.stringify(dom.html());

                localStorage.setItem('inv-0-tmp-row', JSON.stringify(tmpRows));

            }

        }); 
        //

    });

    function roundToTwo(num) {    
        return +(Math.round(num + "e+2")  + "e-2");
    }

    function doCalc() {
        
        var $els = $(".inline-calc:not('.qty, .removed')", document);
        var total = 0;
        var amountPaid = $("#amount_paid").val() || 0;
        $els.each(function(i, el) {
            var $el = $(el);
            var index = $el.data('i');
            var qty = $("#qty-"+index, document).not('.removed').val() || 0;
            var _total = $.trim($("#total-"+index, document).not('.removed').val()) || 0;

            total += _total * qty;
        });

        total = roundToTwo(total);
        amountPaid = roundToTwo(parseFloat(amountPaid));
        $("#total").val(total);
        $("#pending").val(roundToTwo(total - amountPaid));

        $els = null;
    }

    function restoreView() {
        var tmpRows =  JSON.parse(localStorage.getItem('inv-0-tmp-row'));
        var $detContainer = $("#details-container");

        if (tmpRows) {
            for (const key of Object.keys(tmpRows)) {
                var row = JSON.parse(tmpRows[key]);
                var i = key.split("-")[1];
                console.log(key, ' i = ', i);
                $detContainer.append(row);
            }
        }

        doCalc()
    }
</script>
@stop
