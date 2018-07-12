@extends('layouts.tenant')

@section('title')
  {{ __('Branches') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.client.create', $tenant->domain) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('First Name') }}</th>
                  <th>{{ __('Last Name') }}</th>
                  <th>{{ __('Box') }}</th>
                  <th>{{ __('Email') }}</th>
                  <th>{{ __('Telephones') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($clients as $client)
                      
                    <tr>
                      <th scope="row">{{ $client->id }}</th>
                      <td>{{ $client->first_name }}</td>
                      <td>{{ $client->last_name }}</td>
                      <td>{{ $client->boxes->first()->branch_code }}{{ $client->id }}</td>
                      <td>{{ $client->email }}</td>
                      <td>{{ $client->telephones }}</td>
                      <td class="text-center">
                        <a href="{{ route('tenant.client.edit', [$tenant->domain, $client->id]) }}"><i class="fa fa-pencil-square-o"></i></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="#!" class="resend-email-box" data-url="{{ route('tenant.client.welcome.email.resend', $tenant->domain) }}"
                          data-toggle="tooltip" data-placement="left" title="{{ __('Resend welcome email') }}" data-client-id="{{ $client->id }}"
                          >
                          <i class="fa fa-envelope"></i>
                        </a>
                      </td>
                    </tr>

                @endforeach
               
              </tbody>
            </table>
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

          if (!$self.hasClass('resending')) {
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
            })
            .fail(function(hxr) {
              if (error = (hxr.responseJSON.errors || hxr.responseJSON.msg  ) ) {
                swal("", error, "error")
              } else {
                swal("", "{{ __('Error') }}", "error")
              }
              $self.removeClass('resending');
            });

          }

        });
      });
    </script>
@endsection