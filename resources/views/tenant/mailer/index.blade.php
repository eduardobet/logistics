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

           <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.mailer.create', $tenant->domain) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>

          
        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Vol Price') }}</th>
                  <th>{{ __('Real Price') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($mailers as $mailer)
                    <tr>
                      <th scope="row">{{ $mailer->id }}</th>
                      <td>{{ $mailer->name }}</td>
                      <td>{{ $mailer->vol_price }}</td>
                      <td>{{ $mailer->real_price }}</td>
                      <td>{{ $mailer->status }}</td>
                      <td class="text-center">
                        <a href="{{ route('tenant.mailer.edit', [$tenant->domain, $mailer->id]) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
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
