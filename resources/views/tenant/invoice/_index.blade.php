 <table class="table table-hover mg-b-0 pdf-table">
    <thead>
    <tr>
        <th>{{ __('ID') }}</th>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Box') }}</th>
        <th>{{ __('Client') }}</th>
        <th>{{ __('Type') }}</th>
        <th class="pdf-a-right">{{ __('Amount') }}</th>
        <th class="text-center">{{ __('Status') }}</th>
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
        
        @foreach ($invoices as $invoice)
        <tr>
            <th scope="row">{{ $invoice->branch->initial }}-{{ $invoice->id }}</th>
            <td>{{ $invoice->created_at->format('d-m-Y') }}</td>
            <td>{{ $invoice->branch->code }}{{ $invoice->client->manual_id_dsp }}</td>
            <td>{{ $invoice->client->full_name }}</td>
            <td>
                @if ($invoice->warehouse_id)
                    <b>{{ __('Warehouse') }}</b>
                @else
                    <b>{{ __('Internet') }}</b>   
                @endif
            </td>
            <td class="pdf-a-right">{{ $sign }} {{ number_format($invoice->total, 2) }}</td>
            <td class="text-center" id="status-text-{{ $invoice->id }}">
            @if ($invoice->is_paid)
                <span class="badge badge-success">{{ __('Paid') }}</span>
            @else
                <span class="badge badge-danger">{{ __('Pending') }}</span>
            @endif
            </td>

            @if (!isset($exporting))
            <td class="text-center" style="font-size: 15px">

            @can('edit-invoice')
            <a title="{{ __('Edit') }}" href="{{ route('tenant.invoice.edit', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch_id,]) }}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
            @endcan
            
            &nbsp;&nbsp;&nbsp;

            <?php $pending = $invoice->total - $invoice->payments->sum('amount_paid'); ?>

            <a href="#!" class="{{ $pending ? 'create-payment' : 'already-paid' }}"
                data-url="{{ route('tenant.payment.create', [$tenant->domain, $invoice->id, ]) }}"
                title="{{ __('New payment') }}" data-invoice-id="{{ $invoice->id }}"
                data-toggle="modal"
                data-target="#modal-payment-{{ $invoice->id }}"
                data-index="{{ $invoice->id }}"
                data-pending="{{ $pending }}"
            >
                <i class="fa fa-money"></i></a>
            </button>

            @can('show-invoice')
            &nbsp;&nbsp;&nbsp;
            <a target="_blank" title="{{ __('Show') }}" href="{{ route('tenant.invoice.show', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch_id, 'client_id' => $invoice->client->id, ]) }}"><i class="fa fa-eye"></i></a>
            @endcan

            &nbsp;&nbsp;&nbsp;
            <a title="{{ __('Email') }}" href="#!" class="email-invoice{{ $tenant->email_allowed_dup===$invoice->client->email ? '-nope' : null }}"
                data-toggle="tooltip" data-placement="left" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $invoice->id }}"
                data-url="{{ route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id, ]) }}"
                data-toggle="tooltip" data-placement="left" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $invoice->id }}"
                data-loading-text="<i class='fa fa-spinner fa-spin '></i>"
            ><i class="fa fa-envelope"></i></a>

            </td>
            @endif
        </tr>

        @endforeach

    </tbody>
</table>