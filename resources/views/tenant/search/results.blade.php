@extends('layouts.tenant')

@section('title')
  {{ __('Search') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}
           
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>

        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            @if (isset($noresults))
                <h3>{{ $noresults }}</h3>
            @endif

            @if (isset($client) && $client === true)
                @include('tenant.search._clients')
            @endif

            @if (isset($wh) && $wh === true)
                @include('tenant.search._wh')
            @endif

            @if (isset($inv) && $inv === true)
                @include('tenant.search._inv')
            @endif

            @if (isset($reca) && $reca === true)
                @include('tenant.search._reca')
            @endif

            @if (isset($tracking) && $tracking === true)
                @include('tenant.search._trackings')
            @endif

        </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
      $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        $(".resend-email-box").click(function() {
          var $self = $(this);
          var url = $self.data('url');
          var clientId = $self.data('client-id');
          var $indicator = $("#indicator-"+clientId);

          if (!$self.hasClass('resending')) {
            $indicator.show();
            $self.addClass('resending');
            $.ajax({
              url: url,
              data: {client_id: clientId, _token: "{{ csrf_token() }}"},
              method: 'POST',
            })
            .done(function(data) {
              if (data.error == true) {
                swal("", data.msg, "error");
              } else {
                swal("", data.msg, "success");
              }
              $self.removeClass('resending');
              $indicator.hide();
            })
            .fail(function(hxr) {
              if (error = (hxr.responseJSON.errors || hxr.responseJSON.msg  ) ) {
                swal("", error, "error")
              } else {
                swal("", "{{ __('Error') }}", "error")
              }
              $self.removeClass('resending');
              $indicator.hide();
            });

          }

        });

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
      });
    </script>
@endsection