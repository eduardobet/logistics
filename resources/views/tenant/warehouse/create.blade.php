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
            {!! Form::open(['route' => ['tenant.warehouse.store', $tenant->domain], 'id' => 'frm-wh']) !!}
                
            @if (config("app.migrations.{$tenant->id}.warehouses", false) )
                <div class="row">
                    <div class="col-lg-12">
                        <h4>
                            <label class="badge badge-danger">{{ __('Migration mode') }}...</label>
                        </h4>
                    </div>
                </div>
            @endif
            
            @include('tenant.warehouse._fields', [
                'warehouse' => new \Logistics\DB\Tenant\Warehouse,
                'payment' => new \Logistics\DB\Tenant\Payment,
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
    @include('common._add_more', ['identifier' => "wh-0"]))
    
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
                
                if (!$("#branch_to").val() || !$("#client_id").val()) {
                    swal('', '{{ __("Please select the branch and the client") }}', 'error');
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


            $("#frm-wh").submit(function(e) {
            var $form = $(this).get(0);
            if ($form.checkValidity()){
                @if (config("app.migrations.{$tenant->id}.warehouses", false))

                swal({
                    title: '{{__("Are you sure") }}?',                    
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: '{{ __("No") }}',
                    confirmButtonText: '{{ __("Yes") }}'
                })
                .then((result) => {
                    if (result.value) {
                        $form.submit();
                    }
                });
                @else
                  $form.submit();  
                @endif
            }
            e.preventDefault();
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
            var $trackings = $("#trackings");
            
            var totVolWeight = 0;
            var totRealWeight = 0;
            var totalVol = 0;
            var totalReal = 0;
            var $els = $(".inline-calc:not('.qty, .removed')", document);
            var $branchTo = $('#branch_to');
            var $client = $('#client_id');
            var $mailer = $('#mailer_id');
            var specialRate = $client.find(':selected').attr('data-special_rate') || 'false';
            var payVol = $client.find(':selected').attr('data-pay_volume') || 'false';
            var isDHL = $mailer.find(':selected').attr('data-is_dhl') || 'false';

            var volPrice = 0;
            var realPrice = 0;

            var using = "";

            if (specialRate == 'true') {
                realPrice = $client.find(':selected').attr('data-real_price') || 0;
                using = "Special Rate";
            } else if (payVol == 'true') {
                volPrice = $client.find(':selected').attr('data-vol_price') || 0;
                using = "Pay volume";
            } else {

                if (isDHL) {
                    realPrice = $branchTo.find(':selected').attr('data-dhl_price') || 0;
                } else {
                    realPrice = $branchTo.find(':selected').attr('data-real_price') || 0;
                }

                using = "Global branch";
            }

            console.log(volPrice, realPrice, using)

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

    <script>
    var cache = {};
    $(function() {
        $(".select2ize").each(function() {
            var $self = $(this);
            var $child = $($self.data('child'));
            $child.select2({width: 'resolve'});
        });

        $(".select2ize").change(function() {
            var $self = $(this);
            var value = $self.val();
            var apiurl = $self.data('apiurl');
            var $child = $($self.data('child'));
            var childId = $child.attr('id');
            var $loader = $("#loader-"+childId);

            if (!apiurl) {
                console.error("Api url is not defined");
                return;
            }

            if (value && value != "0") {
                
                if ( items = cache[childId + '.' + value ] ) {
                    select2ize($child, items);
                    return;
                }

                $loader.html('<i class="fa fa-spinner fa-spin"></i>');
                $child.prop("disabled", true).select2();
                apiurl = apiurl.replace(":parentId:", value)

                $.getJSON(apiurl, function(items) {
                    $loader.empty();
                    select2ize($child, items);
                    cache[childId + '.' + value] = items;
                });
            } else {
                select2ize($child, []);
            }
        });
    });

    function select2ize($child, items) {
        var newOptions = '<option value="">---</option>';
        for(var key in items) {
            var obj = items[key];
            var box = obj.branch.code;
            newOptions += `
                <option value='${obj.id}'
                    data-pay_volume='${obj.pay_volume}' data-special_rate='${obj.special_rate}' data-special_maritime='${obj.special_maritime}'
                     data-vol_price='${obj.vol_price}'  data-real_price='${obj.real_price}' data-pay_first_lbs_price='${obj.pay_first_lbs_price}' data-first_lbs_price='${obj.first_lbs_price}' 
                >
                   [${box}${obj.manual_id_dsp}] ${obj.full_name}
                </option>`;
        }
        
        $child.select2('destroy').html(newOptions).prop("disabled", false)
        .select2({width: 'resolve'});
    }
</script>
@stop
