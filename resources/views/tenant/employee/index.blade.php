@extends('layouts.tenant')

@section('title')
  {{ __('Employees') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.admin.employee.create', $tenant->domain) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div  class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('First Name') }}</th>
                  <th>{{ __('Last Name') }}</th>
                  <th>{{ __('Branch') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($employees as $employee)
                    <tr>
                      <th scope="row">{{ $employee->id }}</th>
                      <td>{{ $employee->first_name }}</td>
                      <td>{{ $employee->last_name }}</td>
                      <td>{{ $employee->branches->first()->name }}</td>
                      <td>{{ $employee->type }}</td>
                      <td>{{ $employee->status }}</td>
                      <td>
                        <a href="{{ route('tenant.admin.employee.edit', [$tenant->domain, $employee->id]) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                        
                        @if ($employee->status == 'L')
                        
                        &nbsp;&nbsp;&nbsp;
                        <a href="#!" class="resend-welcome-email" data-url="{{ route('tenant.admin.employee.welcome.email.resend', $tenant->domain) }}"
                          data-toggle="tooltip" data-placement="left" title="{{ __('Resend welcome email') }}" data-employee-id="{{ $employee->id }}"
                          >
                          <i class="fa fa-envelope"></i>
                        </a>
                        @endif

                      </td>
                    </tr>

                @endforeach
               
              </tbody>
            </table>
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
        $(".resend-welcome-email").click(function() {
          var $self = $(this);
          var url = $self.data('url');
          var employeeId = $self.data('employee-id');

          if (!$self.hasClass('resending')) {
            $self.addClass('resending');
            $.ajax({
              url: url,
              data: {employee_id: employeeId, _token: "{{ csrf_token() }}"},
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