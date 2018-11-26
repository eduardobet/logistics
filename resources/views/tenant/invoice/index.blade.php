@extends('layouts.tenant')

@section('title')
  {{ __('Invoices') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

           @can('create-invoice')
            <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>
            @endcan

          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="row mg-b-10">

            <div class="col-lg-2">

                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('From') }}
                        </div>
                    </div>
                    <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('from', date('Y-m-d')) }}" id="from">
                </div>

            </div>

            <div class="col-lg-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('To') }}
                        </div>
                    </div>
                     <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('to', date('Y-m-d')) }}" id="to">
                </div>
            </div>

            <div class="col-lg-3">
                 <select name="branch_id" id="branch_id" class="form-control select2 select2ize" style="width: 100%" data-apiurl="{{ route('tenant.api.clients', [':parentId:']) }}" data-child="#client_id">
                    <option value="">{{ __('Branch') }}</option>
                    @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == $branch->id ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3">
                 {!! Form::select('client_id', ['' => '----'], null, ['class' => 'form-control select2', 'id' => 'client_id', 'width' => '100% !important', ]) !!}
            </div>

            <div class="col-lg-1">
                {!! Form::select('invoice_type', ['' => __('Type'), '1' => __('Warehouse'), '2' => __('Internet') ], null, ['class' => 'form-control select2', 'id' => 'invoice_type', 'width' => '100% !important', ]) !!}
            </div>

             <div class="col-lg-1">
                
                <div class="input-group">
                    <div class="input-group-append">
                        <button class="btn" type="button" id="filter">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="table-responsive-sm">
           
           @include('tenant.invoice._index', ['invoices' => $invoices])

            @if ($searching == 'N')
                <div id="result-paginated" class="mg-t-25">
                    {{ $invoices->links() }}
                </div>
            @endif

          </div>
        
        </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->

@if (isset($payments))
@include('tenant.payment.create', ['payments' => $payments, ])
@else  
@include('tenant.payment.create', [])
@endif



 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')
@include('common._select2ize')
<script>
select2ize = function($child, items) {
    var newOptions = '<option value="">{{ __("Client") }}</option>';
        for(var key in items) {
            var obj = items[key];
            var box = obj.branch.code;
            newOptions += `
                <option value='${obj.id}' ${obj.id=="{{request('client_id', 'NA')}}"?" selected":''}>
                   [${box}${obj.id}] ${obj.full_name}
                </option>`;
        }
        
        $child.select2('destroy').html(newOptions).prop("disabled", false)
        .select2({width: 'resolve', placeholder: '{{ __("Client") }}', language: "{{ config('locale.lang') }}"});
}
</script>

    <script>
    $(function() {
  
        // resend invoice
        $(".email-invoice").click(function(e) {
            var $self = $(this);

            if ($self.hasClass('sending')) return false;

            var url = $self.data('url');
            var loadingText = $self.data('loading-text');
            $self.addClass('sending');

            if ($(this).html() !== loadingText) {
                $self.data('original-text', $(this).html());
                $self.html(loadingText);
            }

            var request = $.ajax({
                method: 'post',
                url: url,
                data: $.extend({
                    _token	: $("input[name='_token']").val(),
                    '_method': 'POST',

                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal(data.msg, "", "success");
                } else {
                    swal(data.msg, "", "error");
                }

                $self.removeClass('sending').html($self.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                swal(textStatus, "", "error");
                $self.removeClass('sending').html($self.data('original-text'));
            });

            e.preventDefault();

        });

        // payment
        var $baseModal = $("#modal-payment");
        var $pAmount = $("#p_amount_paid");
        var $launcher = null
        $(".create-payment").click(function(e) {
            var $self = $(this);
            var index = $self.data('index');
            var pending = $self.data('pending') || 0;
            $launcher = $self;
            $("#p_invoice_id").val(index);
            $pAmount.val(pending).attr("max", pending);

            $baseModal.attr('id', 'modal-payment-'+index);
            $baseModal.on('shown.bs.modal', function () {});
        });

        $('#btn-cancel-payment').click(function() {
            $("#p_amount_paid, #p_payment_method, #p_payment_ref").val("");
        });

        $("#form-payment").submit(function(e) {
            
            var $btnSubmit = $('#btn-submit-payment');
            var url = "{{ route('tenant.payment.store', $tenant->domain) }}";
            var loadingText = $btnSubmit.data('loading-text');

            if ($btnSubmit.html() !== loadingText) {
                $btnSubmit.data('original-text', $btnSubmit.html());
                $btnSubmit.prop('disabled', true).html(loadingText);
            }

            var request = $.ajax({
                method: 'post',
                url: url,
                data: $.extend({
                    _token	: $("input[name='_token']").val(),
                    '_method': 'POST',
                    'invoice_id': $("#p_invoice_id").val(),
                    'amount_paid': $("#p_amount_paid").val(),
                    'payment_method': $("#p_payment_method").val(),
                    'payment_ref': $("#p_payment_ref").val(),
                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal("", data.msg, "success");
                    
                    $launcher.attr('data-pending', data.pending);
                    $pAmount.val(data.pending).attr('max', data.pending);
                    $baseModal.modal('hide');
                    
                    if (!data.pending) {
                        $launcher.removeClass('create-payment').addClass('already-paid');
                        $("#status-text-"+$("#p_invoice_id").val()).html('<span class="badge badge-success">{{ __("Paid") }}</span>');
                        $launcher = null
                    }
                    $("#p_amount_paid, #p_payment_method, #p_payment_ref, #p_invoice_id").val("");

                } else {
                    swal("", data.msg, "error");
                }

                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                
                var error = "{{ __('Error') }}";

                if (jqXHR.responseJSON.msg) {
                    error = jqXHR.responseJSON.msg;
                }
                
                swal("", error, "error");
                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));
            });

            e.preventDefault();
        });


        $('.fc-datepicker').datepicker({
          showOtherMonths: true,
          selectOtherMonths: true,
          language: '{{ config("app.locale") }}',
          format: 'yyyy-mm-dd',
          todayBtn: 'linked'
        });

        $("#branch_id").select2({width: 'resolve', 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#filter").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var invoiceType = $("#invoice_type").val();
            window.location = `{{ route('tenant.invoice.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&invoice_type=${invoiceType}`;
        });

        $("#export-xls, #export-pdf").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var invoiceType = $("#invoice_type").val();
            var pdf = this.id === 'export-pdf' ? '&pdf=1' : '';
            
            if(from && to) window.open(`{{ route('tenant.invoice.export', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&invoice_type=${invoiceType}${pdf}`, '_blank');
        });


    });
</script>
@stop