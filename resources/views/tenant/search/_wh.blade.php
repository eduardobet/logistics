@foreach ($results as $result)
    <div class="list-group list-group-flush">
        <div href="#" class="list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">
            {{ ['A' => __('Air'), 'M' => __('Maritime'), ][$result->type] }}
            </h5>
            <small> {{ $result->client->full_name }} / {{ $result->toBranch ? $result->toBranch->code : null }}{{ $result->client->manual_id_dsp }}</small>
            </div>
            <p class="mb-1">[{{ $result->fromBranch->code }}] {{ $result->fromBranch->name }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                [{{ $result->toBranch->code }}] {{ $result->toBranch->name }}
            </p>
            <small>

            @can('edit-warehouse')
                <a target="_blank" href="{{ route('tenant.warehouse.edit', [$tenant->domain, $result->id]) }}"><i class="fa fa-pencil-square-o"></i> {{ __('Edit') }} </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endcan

            @if ($result->status == 'A')
                
            <a target="_blank" href="{{ route('tenant.warehouse.print-sticker', [$tenant->domain, $result->id ]) }}">
                <i class="fa fa-ticket"></i>
                {{ __('Sticker') }}
            </a>

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-invoice')
                <a target="_blank" href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => $result->client->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-file"></i>
                {{ __('Invoices') }}
                </a>
            @endcan

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-payment')    
            <a target="_blank" href="{{ route('tenant.payment.list', [$tenant->domain, 'client_id' => $result->client->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-money"></i>
                {{ __('Payments') }}
            </a>
            @endcan

           @endif
            
            </small>
        </div>
    </div>
@endforeach