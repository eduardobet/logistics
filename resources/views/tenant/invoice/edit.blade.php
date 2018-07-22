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
            {!! Form::model($invoice, ['route' => ['tenant.invoice.update', $tenant->domain, $invoice->id], 'method' => 'PATCH']) !!}
                @include('tenant.invoice._fields', [
                    'mode' => 'edit',
                    'invoice' => $invoice,
                    'payment' => $invoice->payments->where('is_first', true)->first(),
                ])
                
                {!! Form::hidden('qty', null, ['id' => 'qty',]) !!}
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