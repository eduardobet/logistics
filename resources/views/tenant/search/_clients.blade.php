@foreach ($results as $result)

    <div class="list-group list-group-flush">
        <div href="#" class="bd bd-y bd-success bd-1 list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1 tx-bold">{{ $result->full_name }}</h5>
            <h5 class="tx-bold tx-success">{{ $result->branch ? $result->branch->code : null }}{{ $result->manual_id_dsp }}</h5>
            </div>
            <p class="mb-1">{{ $result->email }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ $result->telephones }}
            </p>

            <?php
                $invoices = $result->clientInvoices->flatten();
                $payments = $invoices->pluck('payments')->flatten();
            ?>

            <h3 class="pull-right tx-bold">
                $ {{ number_format( $invoices->sum('total') - $payments->sum('amount_paid'), 2) }}  <small class="tx-xthin"> P/P</small>
            </h3>
            <p></p>

            <small>
            @can('edit-client')
                <a target="_blank" class="btn btn-primary" href="{{ route('tenant.client.edit', [$tenant->domain, $result->id]) }}"><i class="fa fa-pencil-square-o"></i> {{ __('Edit') }} </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endcan

            <a href="#!" class="btn btn-primary resend-email-box{{ $tenant->email_allowed_dup===$result->email ? '-nope' : null }}" data-url="{{ route('tenant.client.welcome.email.resend', $tenant->domain) }}"
            data-toggle="tooltip" data-placement="top" title="{{ __('Resend welcome email') }}" data-client-id="{{ $result->id }}"
            >
                <i class="fa fa-envelope"></i>
                {{ __('Email') }}
                <i style="display:none" id="indicator-{{ $result->id }}" class="fa fa-spinner fa-spin"></i>
            </a>

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-invoice')
                <a class="btn btn-primary" target="_blank" href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => $result->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-file"></i>
                {{ __('Invoices') }}
                </a>
            @endcan

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-payment')    
            <a class="btn btn-primary" target="_blank" href="{{ route('tenant.payment.list', [$tenant->domain, 'client_id' => $result->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-money"></i>
                {{ __('Payments') }}
            </a>
            @endcan
            
            </small>
        </div>
    </div>
    
@endforeach

<div id="result-paginated" class="mg-t-25">
    <hr>
    {{ $results->appends(['cbranch_id' => request('cbranch_id'), 'q' => request('q') ])->links() }}
</div>