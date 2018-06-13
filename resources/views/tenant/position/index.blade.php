@extends('layouts.tenant')

@section('title')
  {{ __('Positions') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.admin.position.create') }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div class="table table-responsive">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Description') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($positions as $position)
                      
                    <tr>
                      <th scope="row">{{ $position->id }}</th>
                      <td>{{ $position->name }}</td>
                      <td>{{ $position->description }}</td>
                      <td>{{ $position->status }}</td>
                      <td>
                        <a href="{{ route('tenant.admin.position.edit', $position->id) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
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
