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
            <h6 class="slim-pagetitle"> {{ $branch->name }} / FAC-{{ $invoice->id }} </h6>
         </div><!-- slim-pageheader -->

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            {!! Form::model($invoice, ['route' => ['tenant.invoice.update', $tenant->domain, $invoice->id], 'method' => 'PATCH']) !!}
                @include('tenant.invoice._fields', [
                    'mode' => 'edit',
                    'invoice' => $invoice,
                    'payment' => ($payments = $invoice->payments)->where('is_first', true)->first(),
                ])
                
                {!! Form::hidden('qty', null, ['id' => 'qty',]) !!}
            </form>
         </div>

         <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                
                @if ($invoice->creator)
                    <p>{{ __('Created by') }} <b>{{ $invoice->creator->full_name }}</b> | <b>{{ $invoice->created_at->format('d/m/Y') }}</b> | {{ $invoice->created_at->format('g:i A') }} </p>
                @endif

                @if ($invoice->editor)
                    <p>{{ __('Edited by') }} <b>{{ $invoice->editor->full_name }}</b> | <b>{{ $invoice->updated_at->format('d/m/Y') }}</b> | {{ $invoice->updated_at->format('g:i A') }} </p>
                @endif

                <?php $lPayment = $payments->last(); ?>
                @if ($lPayment && $lPayment->creator)
                    <p>{{ __('Last payment by') }} <b>{{ $lPayment->creator->full_name }}</b> | <b>{{ $lPayment->created_at->format('d/m/Y') }}</b> | {{ $lPayment->created_at->format('g:i A') }} </p>
                @endif

                @if ($invoice && $invoice->is_paid)
                    <p>{{ __('Delivered by') }} <b>{{ $lPayment->creator->full_name }}</b> | <b>{{ $lPayment->created_at->format('d/m/Y') }}</b> | {{ $lPayment->created_at->format('g:i A') }} </p>
                @endif
                
                @if ($invoice->status == 'I')
                    <p>{{ __('Deleted by') }} <b>{{ $invoice->editor->full_name }}</b> | <b>{{ $invoice->updated_at->format('d/m/Y') }}</b> | {{ $invoice->updated_at->format('g:i A') }} </p>
                @endif
                
            </div>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.payment.create', ['payments' => $payments])


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


        // resend invoice
        $("#resend-invoice").click(function() {
            var $self = $(this);
            var url = $self.data('url');
            var loadingText = $self.data('loading-text');

            if ($(this).html() !== loadingText) {
                $self.data('original-text', $(this).html());
                $self.prop('disabled', true).html(loadingText);
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

                $self.prop('disabled', false).html($self.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                swal(textStatus, "", "error");
                $self.prop('disabled', false).html($self.data('original-text'));
            });

        });

        // payment
        $('#modal-payment').on('shown.bs.modal', function () {});

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
                    'invoice_id': "{{ $invoice->id }}",
                    'amount_paid': $("#p_amount_paid").val(),
                    'payment_method': $("#p_payment_method").val(),
                    'payment_ref': $("#p_payment_ref").val(),
                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal("", data.msg, "success");
                    $("#p_amount_paid, #p_payment_method, #p_payment_ref").val("");

                    $("#pending").val(data.pending);
                    $("#p_amount_paid").attr('max', data.pending);
                    $('#modal-payment').modal('hide');

                    if (!data.pending) $("#create-payment").prop('disabled', true)

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