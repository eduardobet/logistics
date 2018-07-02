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
            {!! Form::open(['route' => ['tenant.warehouse.store', $tenant->domain]]) !!}
                @include('tenant.warehouse._fields')
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->

<input type="hidden" id="tmp-row" value="">

@include('tenant.common._footer')

@endsection


@section('xtra_scripts')
    <script>
        var cache = {};
        $(function() {
            var loadTpl = true;
            $("#branch_from").change(function() {
                var $self = $(this);
                var code = $self.find(':selected').data('code');
                var dComission = $self.find(':selected').data('dcomission');
                var shouldInvoice = $self.find(':selected').data('should-invoice');
                var $btnInvoiceContainer = $("#btn-invoice-container");
                var $invoiceContainer = $("#invoice-container");

                if (dComission || shouldInvoice ) {
                    $btnInvoiceContainer.show(0);
                } else {
                   $btnInvoiceContainer.hide(0);
                   $invoiceContainer.empty();
                   loadTpl = true;
                }
            });
            
            $("#btn-invoice").click(function() {
                var $self = $(this);
                var url = $self.data('url');
                var loadingText = $self.data('loading-text');
                var $invoiceContainer = $("#invoice-container");

                if (loadTpl) {

                    if ($(this).html() !== loadingText) {
                        $self.data('original-text', $(this).html());
                        $self.prop('disabled', true).html(loadingText);
                    }
                    
                    $.getJSON(url, function(data) {
                        $self.prop('disabled', false).html($self.data('original-text'));
                        cache['data'] = data.view;
                        loadTpl = false;
                        $invoiceContainer.show(0).append(data.view);
                    })
                    .fail(function(xhr) {
                        
                        if (error = (xhr.responseJSON.errors || hxr.responseJSON.msg  ) ) {
                            swal("", error, "error")
                        } else {
                            swal("", "{{ __('Error') }}", "error")
                        }
                        $self.prop('disabled', false).html($self.data('original-text'));
                    });
                }
            });

            // client side calculation
            $(document).on("blur", ".inline-calc", function() {
                doCal()
            });

            $(document).on("click", ".rem-row", function() {
                doCal(true);
            });
        

            //

        });

        function doCal(del) {
            var $totVolWeight = $("#total_volumetric_weight");
            var $totRealWeight = $("#total_real_weight");
            var totVolWeight = 0;
            var totRealWeight = 0;

            $(".inline-calc:not('.qty')", document).each(function(i, el) {
                var $el = $(el);
                var index = $el.data('i');
                var qty = $("#qty-"+i).val();
                var length = $("#length-"+i).val();
                var width = $("#width-"+i).val();
                var height = $("#height-"+i).val();
                var $volWeight = $("#volumetric_weight-"+i);
                var $realWeight = $("#real_weight-"+i);
                totRealWeight += parseFloat($realWeight.val() || '0');

                if (del) console.log($realWeight)

                if (length && width && height) {
                    var volWeight = (length * width * height) / 139;
                    var whole = parseInt(volWeight);
                    var dec = volWeight - whole;
                
                    if (dec > 0) {
                        volWeight = whole + 1;
                    }

                    $volWeight.val(volWeight);
                    totVolWeight += volWeight;
                }
            });

            $totVolWeight.val(totVolWeight)
            $totRealWeight.val(totRealWeight)
        }

    </script>
    @include('common._add_more')
@stop
