@foreach ($results as $result)
    <div class="list-group list-group-flush">
        <div href="#" class="list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">
             $ {{ number_format($result->total, 2) }}
            </h5>
            <small> {{ $result->client->full_name }} / {{ $result->branch ? $result->branch->code : null }}{{ $result->client->manual_id_dsp }}</small>
            </div>
            <p class="mb-1">
                 @if ($result->is_paid)
                    <span class="badge badge-success">{{ __('Paid') }}</span>
                @else
                    <span class="badge badge-danger">{{ __('Pending') }}</span>
                @endif
            </p>
            <small>

            @can('show-invoice')
                <a target="_blank" href="{{ route('tenant.invoice.show', [$tenant->domain, $result->id, 'branch_id' => $result->branch->id, 'client_id' => $result->client->id, ]) }}"><i class="fa fa-eye"></i> {{ __('Show') }} </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endcan

            <a title="{{ __('Email') }}" href="#!" class="email-invoice{{ $tenant->email_allowed_dup===$result->email ? '-nope' : null }}"
                data-toggle="tooltip" data-placement="top" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $result->id }}"
                data-url="{{ route('tenant.invoice.invoice.resend', [$tenant->domain, $result->id, ]) }}"
                data-toggle="tooltip" data-placement="top" title="{{ __('Resend invoice email') }}" data-invoice-id="{{ $result->id }}"
                data-loading-text="<i class='fa fa-spinner fa-spin '></i>"
            >
            <i class="fa fa-envelope"></i>
            {{ __('Send') }}
            </a>

            &nbsp;&nbsp;&nbsp;&nbsp;

            <a target="_blank" href="{{ route('tenant.invoice.print-invoice', [$tenant->domain, $result->id ]) }}" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                <i class="fa fa-print"></i>
                {{ __('Print') }}
            </a>
            
            &nbsp;&nbsp;&nbsp;&nbsp;
            {{ $result->created_at->format('d/m/Y') }}
            
            </small>
        </div>
    </div>
@endforeach