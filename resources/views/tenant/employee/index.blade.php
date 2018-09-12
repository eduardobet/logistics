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
