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
            <h6 class="slim-pagetitle"> {{ $branch->name }} / WH-{{ $warehouse->manual_id_dsp  }} </h6>
         </div><!-- slim-pageheader -->

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        
        @include('tenant.warehouse._resume')
        

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

            {!! Form::model($warehouse, ['route' => ['tenant.warehouse.update', $tenant->domain, $warehouse->id], 'method' => 'PATCH', 'name' => 'frm-edit', 'id' => 'frm-edit', ]) !!}
                @include('tenant.warehouse._fields', [
                    'mode' => 'edit',
                    'invoice' => $invoice,
                    'payment' => ($payments = $invoice->payments)->where('is_first', true)->first(),
                ])
                
                {!! Form::hidden('qty', null, ['id' => 'qty',]) !!}
                {!! Form::hidden('rows', null, ['id' => 'rows',]) !!}
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
    @include('common._add_more', ['identifier' => "wh-{$warehouse->id}"])
    <script>
        var cache = {};
        $(function() {

            var loadTpl = true;

            $("#type").change(function(e) {
                if(this.value == 'M') {
                    document.getElementById("chk-t-real-weight").checked = false;
                    document.getElementById("chk-t-volumetric-weight").checked = false;
                    document.getElementById("total").value = '';
                    doCal();
                } else if (this.value == 'A') {
                    document.getElementById("chk-t-cubic-feet").checked = false;
                    document.getElementById("total").value = '';
                    doCal();
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

            // add row on reader enter
            $(document).on('keypress', '.tracking', function(e) {
                if(e.which == 13) {
                    $(".btn-add-more", document).click();

                    e.preventDefault();
                }
            });

            //
            $('.fc-datepicker').datepicker({
                showOtherMonths: true,
                selectOtherMonths: true,
                language: '{{ config("app.locale") }}',
                format: 'yyyy-mm-dd',
                todayBtn: 'linked'
            });

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
            var $type = $("#type");
            var hasDet = false;
            
            var totVolWeight = 0;
            var totRealWeight = 0;
            var totalVol = 0;
            var totalReal = 0;
            var totalCubicFeet = 0;
            var totalVolPrice = 0;
            var totalRealPrice = 0;
            var totalCubicPrice = 0;
            var $els = $(".inline-calc:not('.qty, .removed')", document);
            var $branchTo = $('#branch_to');
            var $client = $('#client_id');
            var $mailer = $('#mailer_id');
            var specialRate = $client.find(':selected').attr('data-special_rate') || 'false';
            var payVol = $client.find(':selected').attr('data-pay_volume') || 'false';
            var payFirstLbs = $client.find(':selected').attr('data-pay_first_lbs_price') || 'false';
            var payExtraMaritime = $client.find(':selected').attr('data-pay_extra_maritime_price') || 'false';

            var volPrice = 0;
            var realPrice = 0;
            var martitimePrice = 0;
            var extraMartitimePrice = 0;
            var firstLbsPrice = parseFloat($client.find(':selected').attr('data-first_lbs_price') || $branchTo.find(':selected').attr('data-first_lbs_price') || '0');

            var using = "";

            if ($type.val() == 'A') {
                if (specialRate == 'true' || payVol == 'true') {
                    realPrice = parseFloat($client.find(':selected').attr('data-real_price') || $branchTo.find(':selected').attr('data-real_price') || '0');
                    volPrice = parseFloat($client.find(':selected').attr('data-vol_price') || $branchTo.find(':selected').attr('data-vol_price') || '0');
                    using = "Special / Volumetric Rate";
                } else {
                    realPrice = parseFloat($branchTo.find(':selected').attr('data-real_price') || '0');
                    volPrice = parseFloat($branchTo.find(':selected').attr('data-vol_price') || '0');
                    using = "Global branch";
                }
            } else if ($type.val() == 'M') {
                martitimePrice = parseFloat($client.find(':selected').attr('data-maritime_price') || $branchTo.find(':selected').attr('data-maritime_price') || '0');
                extraMartitimePrice = parseFloat($client.find(':selected').attr('data-extra_maritime_price') || $branchTo.find(':selected').attr('data-extra_maritime_price') || '0');

                if (isNaN(martitimePrice)) martitimePrice = 0;
                if (isNaN(extraMartitimePrice)) extraMartitimePrice = 0;
            }

            $els.each(function(i, el) {
                var $el = $(el);
                var index = $el.data('i');
                var qty = $("#qty-"+index).not('.removed').val();
                var length = $("#length-"+index).not('.removed').val();
                var width = $("#width-"+index).not('.removed').val();
                var height = $("#height-"+index).not('.removed').val();
                var $volWeight = $("#volumetric_weight-"+index).not('.removed');
                var $realWeight = $("#real_weight-"+index).not('.removed');
                var $isDHL = $("#is_dhll-"+index).not('.removed');
                totRealWeight += parseFloat($realWeight.val() || '0');

                if (length && width && height) {

                    if ($isDHL && $isDHL.is(':checked')) {
                        realPrice = parseFloat($isDHL.val() || '0');
                        using += ' via DHL';
                    } else {
                        // realPrice = parseFloat($branchTo.find(':selected').attr('data-real_price') || '0');
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

                    console.log('**************************************************** ', (length * width * height) / 1728)

                    totVolWeight += volWeight;
                    totalCubicFeet += cubicFeet;
                    totalVolPrice += volPrice;
                    totalRealPrice += realPrice;

                    totalReal += parseFloat($realWeight.val() || '0') * realPrice;
                    totalVol += parseFloat($volWeight.val() || '0') * volPrice;

                    if (totVolWeight > 20) $("#vol_price-"+index).val(volPrice);
                    else $("#vol_price-"+index).val('');
                    $("#real_price-"+index).val(realPrice);

                    if (totVolWeight > 20) {
                        $("#total-"+index).val(parseFloat($volWeight.val() || '0') * volPrice);
                    } else {
                        $("#total-"+index).val(parseFloat($realWeight.val() || '0') * realPrice);
                    }
                    
                    @if (!request('debug'))
                    console.log('-----calculating...... using = ', using, 'volPrice =', volPrice, 'realPrice = ', realPrice, 'payVol = ', payVol)
                    console.log('realWeight = ', $realWeight.val());
                    console.log('volWeight = ', $volWeight.val());
                    @endif

                    hasDet = true;

                } // if
            }); // each

            @if (!request('debug'))
            console.log('firstLbsPrice = ', firstLbsPrice, 'payFirstLbs = ', payFirstLbs, 'totVolWeight = ', totVolWeight, 'totRealWeight = ', totRealWeight, 'totalCubicFeet = ', totalCubicFeet);
            console.log('realPrice = ', realPrice, 'volPrice = ', volPrice, 'martitimePrice = ', martitimePrice, 'extraMartitimePrice = ', extraMartitimePrice );
            @endif

            if ($type.val() == 'A') {
                if(totVolWeight > 20) {
                    $totVolWeight.val(totVolWeight);
                }
                else {
                    $totVolWeight.val('');
                }
                $totRealWeight.val(totRealWeight);

                if (hasDet && payFirstLbs == 'true' && firstLbsPrice) {
                    
                    if(totVolWeight > 20) {
                        if (totVolWeight > 1) totalVol = ((totVolWeight - 1) * volPrice) + firstLbsPrice;
                        else totalVol = firstLbsPrice * totVolWeight;
                    }

                    if (totRealWeight > 1) totalReal = ((totRealWeight - 1) * realPrice) + firstLbsPrice;
                    else totalReal = totRealWeight * firstLbsPrice;
                }

            } else if ($type.val() == 'M') {
                $totCubicFeet.val(totalCubicFeet);
            }
            
            if ($type.val() == 'A') {
                if(totVolWeight > 20) $("#dsp-t-vol").text(totalVol);
                else $("#dsp-t-vol").text('');
                $("#dsp-t-real").text(totalReal);

                $("#dsp-t-cubic").text('');
            } else if ($type.val() == 'M') {
                if (totalCubicFeet > 30) {
                    var extra = totalCubicFeet - 30;
                    //totalCubicPrice = martitimePrice + (extra * extraMartitimePrice);
                    totalCubicPrice = extraMartitimePrice * totalCubicFeet;
                } else {
                   totalCubicPrice = martitimePrice; 
                }

                $("#dsp-t-cubic").text(totalCubicPrice);
                $("#dsp-t-real").text('');
                $("#dsp-t-vol").text('');
            }

            setter(totalVol, totalReal, totalCubicPrice, totVolWeight);

            $els = null;
            $volWeight = null;
            $realWeight = null;


            $("#rows").val($(".det-row", document).length);

            sisyphusy();

        } // doCal
        
        function setter(totVolPrice, totRealPrice, totalCubicPrice, totVolWeight) {
            var $type = $("#type");
            var $total = $("#total", document);
            var $chkV = $("#chk-t-volumetric-weight", document);
            var $chkR = $("#chk-t-real-weight", document);
            var $chkM = $("#chk-t-cubic-feet", document);

            if ($type.val() == 'A') {

                if (totVolWeight > 20 && totVolPrice > totRealPrice) {
                    $chkR.prop({checked: false, disabled: true}).change();
                    $chkV.prop({checked: true, disabled: false}).change();
                    $chkM.prop({checked: false, disabled: true}).change();
                    $total.val(totVolPrice);
                } else {
                    $chkV.prop({checked: false, disabled: true}).change();
                    $chkR.prop({checked: true, disabled: false}).change();
                    $chkM.prop({checked: false, disabled: true}).change();
                    $total.val(totRealPrice);
                }
            } else {
                $chkM.prop({checked: true, disabled: false}).change();
                $chkV.prop({checked: false, disabled: true}).change();
                $chkR.prop({checked: false, disabled: true}).change();
                $total.val(totalCubicPrice);
            }

             var amountPaid = parseFloat($("#amount_paid", document).val()) || 0;

             if (amountPaid) {
                 var total = $total.val() || 0;
                 var pending = roundToTwo(total - amountPaid);

                if (pending > -1) $("#pending", document).val(pending);
             }

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

            // toggle status
            $("#btn-wh-status-toggle").click(function(){
                var self = $(this);
                var status = self.data('status');

                if (status) {

                    swal({
                        title: '{{ __("Are you sure") }}?',                    
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: '{{ __("No") }}',
                        confirmButtonText: '{{ __("Yes") }}'
                    })
                    .then((result) => {
                        if (notes = result.value) {
                            toggle(self, status);
                        }
                    });
                }
            });

            // partial paymment
            $("#amount_paid", document).blur(function() {
              doCal();
            });

            // preserving details

            restoreView();
        });

        function sisyphusy() {
            @if (!$invoice->total)
            $("#frm-edit").sisyphus({
                locationBased: true,
                excludeFields: $("input[name='_token']"),
                autoRelease: false,
                //onSave: function(){},
                //onRestore: function(){},
            });
            @endif

            @if ($invoice->total || $warehouse->status == 'I')
            localStorage.clear();
            @endif
        }

        function restoreView() {
            @if (!$invoice->total)
            var tmpRows =  JSON.parse(localStorage.getItem('wh-{{ $warehouse->id }}-tmp-row'));
            var $detContainer = $("#details-container");

            if (tmpRows) {
                for (const key of Object.keys(tmpRows)) {
                    var row = JSON.parse(tmpRows[key]);
                    var i = key.split("-")[1];
                    console.log(key, ' i = ', i);
                    $detContainer.append(row);
                    sisyphusy();
                }
            }

            doCal()
            @endif
        }

        function toggle($btn, status) {
            $btn.prop('disabled', true);
            swal({
                title: '{{__("Please indicate why you are :doing the :what", ["doing" => ($warehouse->status == "A" ? __("inactivating") : __("activating") ), "what" => __("Warehouse"), ]) }}',
                input: 'textarea',
                showCancelButton: true,
                inputValidator: (value) => {
                    return !value && '{{ __("Error") }}!'
                }
            }).then((result) => {
                if (notes = result.value) {

                    var request = $.ajax({
                        method: 'post',
                        url: "{{ route('tenant.warehouse.toggle', $tenant->domain) }}",
                        data: $.extend({
                            _token	: $("meta[name='csrf-token']").attr('content'),
                            '_method': 'POST',
                            'warehouse_id': "{{ $warehouse->id }}",
                            'status': status,
                            'notes': notes,
                        }, {})
                    });

                    request.done(function(data){
                        if (data.error == false) {
                            swal("", data.msg, "success").then(function() {
                                window.location.reload(true);
                            });
                        } else {
                            swal("", data.msg, "error");
                        }
                    })
                    .fail(function( jqXHR, textStatus ) {
                        
                        var error = "{{ __('Error') }}";

                        if (jqXHR.responseJSON.msg) {
                            error = jqXHR.responseJSON.msg;
                        }
                        
                        swal("", error, "error");
                        $btn.prop('disabled', false);
                    });

                } else $btn.prop('disabled', false);
            });
        }

        function roundToTwo(num) {    
            return +(Math.round(num + "e+2")  + "e-2");
        }
    </script>
@stop
