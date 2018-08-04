<table class="table table-hover mg-b-0 pdf-table">
    <thead>
    <tr>
        <th>{{ __('ID') }}</th>
        <th class="text-center">#{{ __('Invoice') }}</th>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Client') }}</th>
        <th>{{ __('Box') }}</th>
        <th>{{ __('Amount') }}</th>

        
        @if (!isset($exporting))
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
        @endif
    </tr>
    </thead>
    <tbody>

        
        @foreach ($payments->groupBy('payment_method') as $key => $groups)
            
        <tr>
            <td colspan="5">
                <b>{{ [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check')][$key] }}</b>
            </td>
            <td>
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
                        <a target="_blank" title="{{ __('Edit') }}" href="{{ route('tenant.invoice.edit', [$tenant->domain, $payment->invoice_id, 'branch_id' => $payment->invoice_branch_id,]) }}"><i class="fa fa-external-link" aria-hidden="true"></i></a> 
                    @else
                        ({{ $payment->invoice_id }})
                    @endcan
                @else
                    {{ $payment->invoice_id }}  
                @endif
            </td>
            <td>{{ $payment->created_at_dsp }}</td>
            <td>{{ $payment->client_full_name }}</td>
            <td>{{ $payment->client_box }}{{ $payment->client_id }}</td>
            <td>{{ $sign }} {{ number_format($payment->amount_paid, 2) }}</td>
            @if (!isset($exporting))
                <td class="text-center" style="font-size: 15px"></td>
            @endif
            </tr>

        @endforeach
        @endforeach

    </tbody>
</table>