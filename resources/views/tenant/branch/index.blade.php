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



           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.admin.branch.create', $tenant->domain) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div class="table table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Code') }}</th>
                  <th>{{ __('Initial') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Color') }}</th>
                  <th>{{ __('Address') }}</th>
                  <th>{{ __('Telephones') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($branches as $branch)
                      
                    <tr>
                      <th scope="row">{{ $branch->id }}</th>
                      <td>{{ $branch->code }}</td>
                      <td>{{ $branch->initial }}</td>
                      <td>{{ $branch->name }}</td>
                      <td>
                        @if ($branch->color)
                          <span class="{{ $branch->color }}">
                            <i class="fa fa-check-square-o"></i>  
                          </span>
                        @endif
                      </td>
                      <td>{{ $branch->address }}</td>
                      <td>{{ $branch->telephones }}</td>
                      <td>
                        <a href="{{ route('tenant.admin.branch.edit', [$tenant->domain, $branch->id]) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
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
