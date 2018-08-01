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
           
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>

        </div><!-- slim-pageheader -->

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th class="text-center">#{{ __('Invoice') }}</th>
                  <th>{{ __('Date') }}</th>
                  <th>{{ __('Client') }}</th>
                  <th>{{ __('Box') }}</th>
                  <th>{{ __('Amount') }}</th>
                  <th class="text-center">
                    @can('create-payment')
                        <a class="btn btn-sm btn-outline-dark" href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
                            <i class="fa fa-plus"></i>
                        </a>

                        <a class="btn btn-sm btn-outline-dark" href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
                            <i class="fa fa-file-excel-o"></i>
                        </a>

                        <a class="btn btn-sm btn-outline-dark" href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
                            <i class="fa fa-file-pdf-o"></i>
                        </a>

                    @endcan
                  </th>
                </tr>
              </thead>
              <tbody>

                  
                  @foreach ($payments->groupBy('payment_method') as $key => $groups)
                      
                    <tr>
                        <td colspan="6">{{ [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check')][$key] }}</td>
                    </tr>
                        
                    @foreach ($groups as $payment)
                        <tr>
                        <th scope="row">{{ $payment->id }}</th>
                        <td class="text-center">

                            @can('edit-invoice')
                                <a target="_blank" title="{{ __('Edit') }}" href="{{ route('tenant.invoice.edit', [$tenant->domain, $payment->invoice_id, 'branch_id' => $payment->invoice_branch_id,]) }}"><i class="fa fa-external-link" aria-hidden="true"></i></a> 
                            @else    
                                ({{ $payment->invoice_id }})
                            @endcan

                        </td>
                        <td>{{ $payment->created_at_dsp }}</td>
                        <td>{{ $payment->client_full_name }}</td>
                        <td> {{ $payment->client_box }}{{ $payment->client_id }}</td>
                        <td>$ {{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="text-center" style="font-size: 15px">
                        </td>
                        </tr>

                    @endforeach
                  @endforeach

              </tbody>
            </table>

            
            @if ($searching == 'N')
                <div id="result-paginated" class="mg-t-25">
                    {{ $payments->links() }}
                </div>
            @endif

          </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
    $(function() {

    });
</script>
@stop