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
            <h6 class="slim-pagetitle"> {{ $branch->name }} / WH-{{ $warehouse->id  }} </h6>
         </div><!-- slim-pageheader -->

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        
        @include('tenant.warehouse._resume')
        

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

            {!! Form::model($warehouse, ['route' => ['tenant.warehouse.update', $tenant->domain, $warehouse->id], 'method' => 'PATCH', 'id' => 'frm-edit', ]) !!}
                @include('tenant.warehouse._fields', [
                    'mode' => 'edit',
                    'invoice' => $invoice,
                ])
                
                {!! Form::hidden('qty', null, ['id' => 'qty',]) !!}
            </form>
            
         </div>


        <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                <p>{{ __('Created by') }} <b>{{ $warehouse->creator->full_name }}</b> | <b>{{ $warehouse->created_at->format('d/m/Y') }}</b> | {{ $warehouse->created_at->format('g:i A') }} </p>
                
                @if ($warehouse->editor)
                    <p>{{ __('Edited by') }} <b>{{ $warehouse->editor->full_name }}</b> | <b>{{ $warehouse->updated_at->format('d/m/Y') }}</b> | {{ $warehouse->updated_at->format('g:i A') }} </p>
                @endif

                @if ($invoice && $invoice->created_at)
                    <p>{{ __('Invoiced by') }} <b>{{ $invoice->creator->full_name }}</b> | <b>{{ $invoice->created_at->format('d/m/Y') }}</b> | {{ $warehouse->created_at->format('g:i A') }} </p>
                @endif

                @if ($invoice && $invoice->is_paid)
                    <?php $payments = $invoice->payments; $lPayment = $payments->last(); ?>
                    <p>{{ __('Delivered by') }} <b>{{ $lPayment->creator->full_name }}</b> | <b>{{ $lPayment->created_at->format('d/m/Y') }}</b> | {{ $lPayment->created_at->format('g:i A') }} </p>
                @endif
                
            </div>
         </div>




    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection

@section('xtra_styles')
    <style>input[data-readonly] {pointer-events: none;}</style>
@endsection


@section('xtra_scripts')
    @include('common._add_more')
    <script>
        var cache = {};
        $(function() {

            var loadTpl = true;

            $("#type").change(function(e) {
                if(this.value == 'M') {
                    document.getElementById("chk-t-real-weight").checked = false;
                    document.getElementById("chk-t-volumetric-weight").checked = false;
                    document.getElementById("total").value = '';
                } else if (this.value == 'A') {
                    document.getElementById("chk-t-cubic-feet").checked = false;
                    document.getElementById("total").value = '';
                } else {
                    document.getElementById("total").value = '';
                    document.getElementById("chk-t-real-weight").checked = false;
                    document.getElementById("chk-t-volumetric-weight").checked = false;
                    document.getElementById("chk-t-cubic-feet").checked = false;
                }
            })

            doCal();

            //
            $("#client_id").change(function() {
                var $btnAddDet = $("#btn-add-details");
                var $invoiceDetContainer = $("#details-container");
                if (this.value) {
                    $btnAddDet.removeAttr('disabled')
                }
                else {
                    $btnAddDet.attr('disabled', true)
                    $invoiceDetContainer.empty();
                    $invoiceDetContainer = null;
                }
            });
            
            $("#btn-invoice").click(function() {
                
                if (!$("#client_id").val()) {
                    swal('', '{{ __("Please select the client") }}', 'error');
                    return;
                }

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
                        $btnAddDet = $(data.view).find('#btn-add-details');
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

            $(document).on("click", ".is_dhll", function() {
                doCal()
            });

            $(document).on("click", ".rem-row", function() {
                doCal();
            });

            $("#maritime_rate").blur(function(){
                doCal();
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
            var $totCubicFeet = $("#total_cubic_feet");
            var $trackings = $("#trackings");
            var maritimeRate = parseFloat($("#maritime_rate").val() || '0');
            var $type = $("#type");
            
            var totVolWeight = 0;
            var totRealWeight = 0;
            var totalVol = 0;
            var totalReal = 0;
            var totalCubicFeet = 0;
            var totalVolPrice = 0;
            var totalRealPrice = 0;
            var $els = $(".inline-calc:not('.qty, .removed')", document);
            var $branchTo = $('#branch_to');
            var $client = $('#client_id');
            var $mailer = $('#mailer_id');
            var specialRate = $client.find(':selected').attr('data-special_rate') || 'false';
            var payVol = $client.find(':selected').attr('data-pay_volume') || 'false';

            var volPrice = 0;
            var realPrice = 0;

            var using = "";

            if (specialRate == 'true') {
                realPrice = parseFloat($client.find(':selected').attr('data-real_price') || '0');
                using = "Special Rate";
            } else if (payVol == 'true') {
                volPrice = parseFloat($client.find(':selected').attr('data-vol_price') || '0');
                using = "Pay volume";
            } else {
                using = "Global branch";
            }

            $els.each(function(i, el) {
                var $el = $(el);
                var index = $el.data('i');
                var qty = $("#qty-"+i).not('.removed').val();
                var length = $("#length-"+i).not('.removed').val();
                var width = $("#width-"+i).not('.removed').val();
                var height = $("#height-"+i).not('.removed').val();
                var $volWeight = $("#volumetric_weight-"+i).not('.removed');
                var $realWeight = $("#real_weight-"+i).not('.removed');
                var $isDHL = $("#is_dhll-"+i).not('.removed');
                totRealWeight += parseFloat($realWeight.val() || '0');

                if (length && width && height) {

                    if ($isDHL && $isDHL.is(':checked')) {
                        realPrice =parseFloat($isDHL.val() || '0');
                        using += ' via DHL';
                    } else {
                        realPrice = parseFloat($branchTo.find(':selected').attr('data-real_price') || '0');
                    }

                    $trackings.prop('readonly', false)

                    var volWeight = (length * width * height) / 166;
                    var cubicFeet = (length * width * height) / 1728;
                    var whole = parseInt(volWeight);
                    var wholeCubic = parseInt(cubicFeet);
                    var dec = volWeight - whole;
                    var decCubic = cubicFeet - wholeCubic;
                
                    if (dec > 0) {
                        volWeight = whole + 1;
                    }

                    if (decCubic > 0) {
                        cubicFeet = wholeCubic + 1;
                    }

                    $volWeight.val(volWeight);
                    totVolWeight += volWeight;
                    totalCubicFeet += cubicFeet;
                    totalVolPrice += volPrice;
                    totalRealPrice += realPrice;

                    totalReal += parseFloat($realWeight.val() || '0') * realPrice;
                    totalVol += parseFloat($volWeight.val() || '0') * volPrice;

                    console.log('-----calculating......','volPrice =', volPrice, 'realPrice = ', realPrice, 'payVol = ', payVol)
                    console.log('realWeight = ', $realWeight.val());
                    console.log('volWeight = ', $volWeight.val());

                } // if
            }); // each


            //totalVol = parseFloat(totVolWeight) *  parseFloat(totalVolPrice);
            //totalReal = parseFloat(totRealWeight) *  parseFloat(totalRealPrice);
            $totVolWeight.val(totVolWeight)
            $totRealWeight.val(totRealWeight)
            $totCubicFeet.val(totalCubicFeet)

            $("#dsp-t-vol").text(totalVol);
            $("#dsp-t-real").text(totalReal);
            $("#dsp-t-cubic").text(totalCubicFeet*maritimeRate);

            $els = null;
            $volWeight = null;
            $realWeight = null;
        }

        $(document).on('click', "#chk-t-volumetric-weight, #chk-t-real-weight, #chk-t-cubic-feet", function(e) {
            var $total = $("#total", document);
            var $type = $("#type");
            if(this.id == 'chk-t-volumetric-weight') {
                if ($type.val() == 'M') return false;
                document.getElementById("chk-t-real-weight").checked = false;
                document.getElementById("chk-t-cubic-feet").checked = false;
                $total.val($("#dsp-t-vol").text());
            } else if(this.id == 'chk-t-real-weight') {
                if ($type.val() == 'M') return false;
                document.getElementById("chk-t-volumetric-weight").checked = false;
                document.getElementById("chk-t-cubic-feet").checked = false;
                $total.val($("#dsp-t-real").text());
            } else if(this.id == 'chk-t-cubic-feet') {
                if ($type.val() == 'A') return false;
                document.getElementById("chk-t-real-weight").checked = false;
                document.getElementById("chk-t-volumetric-weight").checked = false;
                $total.val($("#dsp-t-cubic").text());
            }

            if (!document.getElementById("chk-t-real-weight").checked && !document.getElementById("chk-t-volumetric-weight").checked && !document.getElementById("chk-t-cubic-feet").checked) $total.val('')
        });

        $(function() {
            $("#frm-edit").submit(function(e) {
                if (!parseFloat($("#total").val() || '0')) {
                    swal('', "{{ __('validation.required', ['attribute' => __('Total to invoice') ]) }}" , 'error');
                    e.preventDefault();
                } else this.submit()
            });
        })
    </script>
@stop
