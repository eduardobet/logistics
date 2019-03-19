@extends('layouts.tenant')

@section('title')
  {{ __('Clients') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
           {{ Breadcrumbs::render() }}

           @can('create-client')
              <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.client.create', $tenant->domain) }}">
                <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
              </a>
            @endcan

          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="row mg-b-10">
            
            <div class="col-lg-10">
                <div class="input-group">
                    <select name="branch_id" id="branch_id" class="form-control select2" style="width: 100%">
                        <option value="">{{ __('Branch') }}</option>
                        @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == request('branch_id', $branch->id) ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-lg-2">
                <div class="input-group">
                    <button class="btn" type="button" id="btn-filter">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            
        </div>

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
                      <th scope="row">{{ $client->manual_id_dsp }}</th>
                      <td>{{ $client->first_name }}</td>
                      <td>{{ $client->last_name }}</td>
                      <td>{{ $client->branch ? $client->branch->code : null }}{{ $client->manual_id_dsp }}</td>
                      <td>{{ $client->email }}</td>
                      <td>{{ $client->telephones }}</td>
                      <td class="text-center">
                        @can('edit-client')
                          <a href="{{ $client->branch->id != $branch->id && (!$user->isSuperAdmin() || !$user->isAdmin()) ? '#' : route('tenant.client.edit', [$tenant->domain, $client->id]) }}"><i class="fa fa-pencil-square-o"></i></a>
                          &nbsp;&nbsp;&nbsp;&nbsp;
                        @endcan
                        <a href="#!" class="resend-email-box{{ $tenant->email_allowed_dup===$client->email ? '-nope' : null }}" data-url="{{ route('tenant.client.welcome.email.resend', $tenant->domain) }}"
                          data-toggle="tooltip" data-placement="left" title="{{ __('Resend welcome email') }}" data-client-id="{{ $client->id }}"
                          >
                          <i class="fa fa-envelope"></i>
                        </a>

                        @if (auth()->user()->isSuperAdmin())
                          &nbsp;&nbsp;&nbsp;&nbsp;    
                          <button type="button" class="btn btn-link create-client-user" title="{{ __('Creating :what', ['what' => __('User') ]) }}"
                            data-toggle="tooltip" data-placement="left"

                            data-client_id = "{{ $client->id }}"
                            data-first_name = "{{ $client->first_name }}"
                            data-last_name = "{{ $client->last_name }}"
                            data-email = "{{ $client->email }}"
                            data-telephones = "{{ $client->telephones }}"
                            data-branch_id = "{{ $client->branch_id }}"
                            >
                            <fa class="fa fa-user"></fa>
                          </button>
                        @endif

                      </td>
                    </tr>

                @endforeach
               
              </tbody>
            </table>

            <div id="result-paginated" class="mg-t-25">
                <hr>
                {{ $clients->appends(['branch_id' => request('branch_id') ])->links() }}
            </div>

          </div>
        
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
        

        // searching
        $("#branch_id").select2({width: 'resolve', allowClear: true, 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#btn-filter").click(function() {
            var branch = $("#branch_id").val();
            window.location = `{{ route('tenant.client.list', $tenant->domain) }}?branch_id=${branch}`;
        });

        @if (auth()->user()->isSuperAdmin())
        // create user
          $(".create-client-user").click(function() {
            var self = $(this);
            self.prop('disabled', true);

            swal({
                title: '{{__("Please indicate a password for the user") }}.',
                input: 'password',
                showCancelButton: true,
                inputValidator: (value) => {
                    return !value && '{{ __("Error") }}!'
                }
            }).then((result) => {
              if (password = result.value) {
                createUser(self, password);
              } else self.prop('disabled', false);
            });

          });
          @endif

      });

      @if (auth()->user()->isSuperAdmin())
      function createUser(self, password) {
          $.ajax({
              url: '{{ route("tenant.admin.user-client.store", $tenant->domain) }}',
              data: {
                client_id: self.data('client_id'),
                first_name: self.data('first_name'),
                last_name: self.data('last_name'),
                email: self.data('email'),
                telephones: self.data('telephones'),
                branch_id: self.data('branch_id'),
                password: password,
                _token: "{{ csrf_token() }}",
                _method: "POST",
              },
              method: 'POST',
            })
            .done(function(data) {
              if (data.error == true) {
                swal("", data.msg, "error");
                self.prop('disabled', false);
              } else {
                swal("", data.msg, "success");
              }
            })
            .fail(function(hxr) {
              if (error = (hxr.responseJSON.errors || hxr.responseJSON.msg  ) ) {
                swal("", error, "error")
              } else {
                swal("", "{{ __('Error') }}", "error")
              }
              self.prop('disabled', false);
            });
      }
      @endif
    </script>
@endsection