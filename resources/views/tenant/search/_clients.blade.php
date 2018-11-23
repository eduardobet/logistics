@foreach ($results as $result)

    <div class="list-group list-group-flush">
        <div href="#" class="list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">{{ $result->full_name }}</h5>
            <small>{{ $result->branch ? $result->branch->code : null }}{{ $result->id }}</small>
            </div>
            <p class="mb-1">{{ $result->email }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ $result->telephones }}
            </p>
            <small>

            @can('edit-client')
                <a target="_blank" href="{{ route('tenant.client.edit', [$tenant->domain, $result->id]) }}"><i class="fa fa-pencil-square-o"></i> {{ __('Edit') }} </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endcan

            <a href="#!" class="resend-email-box" data-url="{{ route('tenant.client.welcome.email.resend', $tenant->domain) }}"
            data-toggle="tooltip" data-placement="top" title="{{ __('Resend welcome email') }}" data-client-id="{{ $result->id }}"
            >
                <i class="fa fa-envelope"></i>
                {{ __('Email') }}
                <i style="display:none" id="indicator-{{ $result->id }}" class="fa fa-spinner fa-spin"></i>
            </a>

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-invoice')
                <a target="_blank" href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => $result->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-file"></i>
                {{ __('Invoices') }}
                </a>
            @endcan

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-payment')    
            <a target="_blank" href="{{ route('tenant.payment.list', [$tenant->domain, 'client_id' => $result->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-money"></i>
                {{ __('Payments') }}
            </a>
            @endcan
            
            </small>
        </div>
    </div>
    
@endforeach