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

           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.client.create') }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div class="table-responsive">
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
                      <td>
                        <a href="{{ route('tenant.client.edit', $client->id) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
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
