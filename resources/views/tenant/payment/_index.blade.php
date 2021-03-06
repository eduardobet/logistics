<table class="table table-hover mg-b-0 pdf-table">
    <thead>
    <tr>
        <th>{{ __('ID') }}</th>
        <th class="text-center">#{{ __('Invoice') }}</th>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Client') }}</th>
        <th>{{ __('Box') }}</th>
        <th class="pdf-a-right">{{ __('Amount') }}</th>

        
        @if (!isset($exporting))
            <th class="text-center">

                @can('show-payment')
                    
                    <a id="export-xls" class="btn btn-sm btn-outline-dark" href="#!" title="Excel">
                        <i class="fa fa-file-excel-o"></i>
                    </a>
                    
                    <a id="export-pdf" class="btn btn-sm btn-outline-dark" href="#!" title="PDF">
                        <i class="fa fa-file-pdf-o"></i>
                    </a>
                @endcan

            </th>
        @endif
    </tr>
    </thead>
    <tbody>

        
        @foreach ($payments->groupBy('payment_method') as $key => $groups)
            
        <tr>
            <td colspan="5" class="pdf-mt-5">
                <b>{{ [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check')][$key] }}</b>
            </td>
            <td class="pdf-a-right">
                <b>{{ $sign }} {{ number_format($groups->sum('amount_paid'), 2) }}</b>
            </td>

            @if (!isset($exporting))
                <td></td>
            @endif
        </tr>
            
        @foreach ($groups as $payment)
            <tr>
            <th scope="row">{{ $payment->id }}</th>
            <td class="text-center">
                @if (!isset($exporting))
                    @can('edit-invoice')
                        <a  title="{{ __('Edit') }}" href="{{ route('tenant.invoice.show', [$tenant->domain, $payment->invoice_id, 'branch_id' => $payment->invoice_branch_id, 'client_id' => $payment->client_id, ]) }}"><i class="fa fa-external-link" aria-hidden="true"></i> ({{ $payment->branch_initial }}-{{ $payment->invoice_manual_id }}) </a> 
                    @else
                        ({{ $payment->branch_initial }}-{{ $payment->invoice_manual_id }})
                    @endcan
                @else
                    {{ $payment->branch_initial }}-{{ $payment->invoice_manual_id }}  
                @endif
            </td>
            <td>{{ $payment->created_at_dsp }}</td>
            <td>{{ $payment->client_full_name }}</td>
            <td>{{ $payment->client_box }}{{ str_pad($payment->client_manual_id, 2, '0', STR_PAD_LEFT) }}</td>
            <td class="pdf-a-right">{{ $sign }} 
                @if (!empty($show_total))
                {{ number_format($payment->amount_paid, 2) }}
                @else
                {{ number_format($payment->amount_paid, 2, ".", "") }}
                @endif
            </td>
            @if (!isset($exporting))
                <td class="text-center" style="font-size: 15px">
                    @can('show-payment')
                        <a title="{{ __('Show') }}" href="{{ route('tenant.payment.show', [$tenant->domain, $payment->id, ]) }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                    @endcan
                </td>
            @endif
            </tr>

        @endforeach
        @endforeach

        <tr>
            <td colspan="5" class="pdf-mt-5 tx-right pdf-a-right">
                Total:
            </td>
            <td class="pdf-a-right">
                <b>{{ $sign }} 

                @if (!empty($show_total))
                {{ number_format($payments->sum('amount_paid'), 2) }}
                @else
                {{ number_format($payments->sum('amount_paid'), 2, ".", "") }}
                @endif
                </b>
            </td>

            @if (!isset($exporting))
                <td></td>
            @endif
        </tr>

    </tbody>
</table>