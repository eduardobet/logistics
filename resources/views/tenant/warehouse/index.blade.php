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

           @can('create-warehouse')
             <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.warehouse.create', $tenant->domain) }}">
                <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
             </a>
            @endcan
          
        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Issuer branch') }}</th>
                  <th>{{ __('Destination branch') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($warehouses as $warehouse)
                    <tr>
                      <th scope="row">{{ $warehouse->id }}</th>
                      <td>{{ ['A' => __('Air'), 'M' => __('Maritime')][$warehouse->type] }}</td>
                      <td>{{ $warehouse->fromBranch->name }}</td>
                      <td>{{ $warehouse->toBranch->name }}</td>
                      <td class="text-center">
                          @can('edit-warehouse')
                            <a href="{{ route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]) }}"><i class="fa fa-pencil-square-o"></i></a>
                          @endcan
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
