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
                @include('tenant.warehouse._fields', [
                    'warehouse' => new \Logistics\DB\Tenant\Warehouse,
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
    @include('common._select2ize')
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
                
                if (!$("#mailer_id").val()) {
                    swal('', '{{ __("Please select the mailer") }}', 'error');
                    return;
                }

                var $self = $(this);
                var url = $self.data('url');
                var loadingText = $self.data('loading-text');
                var $invoiceContainer = $("#invoice-container");
                var $notes = $("#invoice-notes");

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

                        $notes.removeClass('d-none')
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
                doCal();
            });

            $("#mailer_id").change(function() {
                if (!this.value) $("#btn-wh-save").prop('disabled', true)
                else {
                    doCal();
                    $("#btn-wh-save").prop('disabled', false)
                }
            });

            // counter
            $("#trackings").keyup(function(e) {
                if (e.keyCode == 13){
                    if (trackings = $.trim(this.value) ) {
                        countTracking(trackings);
                    }
                }
            });

            $("#trackings").blur(function(e) {
                if (trackings = $.trim(this.value)) {
                    countTracking(trackings);
                } else {
                   $("#qty").val('');
                   $("#qty-dsp").text(0) 
                }
            });
            //

        });

        function countTracking(trackings) {
            var qty = (trackings.match(/\r?\n/g) || '').length + 1;
            $("#qty").val( qty );
            $("#qty-dsp").text(qty);
        }

        function doCal() {
            var $totVolWeight = $("#total_volumetric_weight");
            var $totRealWeight = $("#total_real_weight");
            var $trackings = $("#trackings");
            
            var totVolWeight = 0;
            var totRealWeight = 0;
            var totalVol = 0;
            var totalReal = 0;
            var $els = $(".inline-calc:not('.qty, .removed')", document);
            var $mailer = $('#mailer_id');
            var volPrice = $mailer.find(':selected').attr('data-vol_price') || 0;
            var realPrice = $mailer.find(':selected').attr('data-real_price') || 0;

            $els.each(function(i, el) {
                var $el = $(el);
                var index = $el.data('i');
                var qty = $("#qty-"+i).not('.removed').val();
                var length = $("#length-"+i).not('.removed').val();
                var width = $("#width-"+i).not('.removed').val();
                var height = $("#height-"+i).not('.removed').val();
                var $volWeight = $("#volumetric_weight-"+i).not('.removed');
                var $realWeight = $("#real_weight-"+i).not('.removed');
                totRealWeight += parseFloat($realWeight.val() || '0');

                if (length && width && height) {
                    $trackings.prop('readonly', false)

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

            totalVol = parseFloat(totVolWeight) *  parseFloat(volPrice);
            totalReal = parseFloat(totRealWeight) *  parseFloat(realPrice);
            $totVolWeight.val(totVolWeight)
            $totRealWeight.val(totRealWeight)

            $("#dsp-t-vol").text(totalVol);
            $("#dsp-t-real").text(totalReal);

            $els = null;
            $volWeight = null;
            $realWeight = null;
        }

        $(document).on('click', "#chk-t-volumetric-weight, #chk-t-real-weight", function() {
            var $total = $("#total", document);
            if(this.id == 'chk-t-volumetric-weight') {
                document.getElementById("chk-t-real-weight").checked = false;
                $total.val($("#dsp-t-vol").text());
            } else if(this.id == 'chk-t-real-weight') {
                document.getElementById("chk-t-volumetric-weight").checked = false;
                $total.val($("#dsp-t-real").text());
            }
        });
    </script>
@stop
