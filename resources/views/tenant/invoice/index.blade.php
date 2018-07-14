@extends('layouts.tenant')

@section('title')
  {{ __('Invoices') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

           @can('create-invoice')
            <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.invoice.create', $tenant->domain) }}">
               <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
            </a>
            @endcan

          
        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Date') }}</th>
                  <th>{{ __('Client') }}</th>
                  <th>{{ __('Amount') }}</th>
                  <th class="text-center">{{ __('Status') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($invoices as $invoice)
                    <tr>
                      <th scope="row">{{ $invoice->id }}</th>
                      <td>{{ $invoice->created_at->format('d-m-Y') }}</td>
                      <td>{{ $invoice->client->full_name }}</td>
                      <td>$ {{ number_format($invoice->total, 2) }}</td>
                      <td class="text-center">
                        @if ($invoice->status == 'P')
                            <span class="badge badge-success">{{ __('Paid') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('Pending') }}</span>
                        @endif
                      </td>
                      <td class="text-center" style="font-size: 15px">

                        @can('edit-invoice')
                        <a title="{{ __('Edit') }}" href="{{ route('tenant.invoice.edit', [$tenant->domain, $invoice->id]) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                        @endcan
                       
                        &nbsp;&nbsp;&nbsp;

                        <a title="{{ __('Payment') }}" href="{{ route('tenant.invoice.edit', [$tenant->domain, $invoice->id]) }}"><i class="fa fa-money" aria-hidden="true"></i></a>

                        &nbsp;&nbsp;&nbsp;
                        <a title="{{ __('Email') }}" href="#!" class="email-invoice" data-url="{{ route('tenant.invoice.edit', [$tenant->domain, $invoice->id]) }}"
                            data-toggle="tooltip" data-placement="left" title="{{ __('Resend welcome email') }}" data-client-id="{{ $client->id }}"
                        ><i class="fa fa-envelope"></i></a>

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

            <div id="result-paginated" class="mg-t-25">
                {{ $invoices->links() }}
            </div>

          </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection
