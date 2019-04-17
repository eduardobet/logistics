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
                </tr>
              </thead>
              <tbody>
                      
                <tr>
                    <th scope="row">{{ $client->manual_id_dsp }}</th>
                    <td>{{ $client->first_name }}</td>
                    <td>{{ $client->last_name }}</td>
                    <td>{{ $client->branch ? $client->branch->code : null }}{{ $client->manual_id_dsp }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->telephones }}</td>
                </tr>

                <tr>
                    <td colspan="6"><h3>{{ __('Invoices') }}</h3></td>
                </tr>

                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Created at') }}</th>
                  <th>{{ __('Tipo') }}</th>
                  <th>{{ __('Total') }}</th>
                  <th>{{ __('Pending') }}</th>
                  <th></th>
                </tr>

                @foreach ($client->clientInvoices as $i => $invoice)
                    <tr>
                        <td>{{ $invoice->branch->initial }}-{{ $invoice->manual_id_dsp  }}</td>
                        <td>{{ $invoice->created_at->format('Y-m-d')  }}</td>
                        <td>{{ $invoice->warehouse_id ? __('Warehouse') : 'Internet'  }}</td>
                        <td>$ {{ number_format($invoice->total, 2)  }}</td>
                        <td>$ {{ number_format($invoice->pending, 2)  }}</td>
                        <td>
                            @can('show-invoice')
                            <a  title="{{ __('Show') }}" href="{{ route('tenant.invoice.show', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch_id, 'client_id' => $client->id, ]) }}"><i class="fa fa-eye"></i></a>
                            @endcan
                        </td>
                    </tr>

                    @if($loop->last)
                        <tr>
                            <td colspan="3" style="text-align: right">Total</td>
                            <td>$ {{ number_format($client->clientInvoices->sum('total'), 2)  }}</td>
                            <td>$ {{ number_format($client->clientInvoices->sum('pending'), 2)  }}</td>
                            <td></td>
                        </tr>
                    @endif
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
        // searching
        $("#branch_id").select2({width: 'resolve', allowClear: true, 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#btn-filter").click(function() {
            var branch = $("#branch_id").val();
            window.location = `{{ route('tenant.outstandings.list', $tenant->domain) }}?branch_id=${branch}`;
        });

      });
    </script>
@endsection