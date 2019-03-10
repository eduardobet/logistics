@foreach ($results as $result)
    <div class="list-group list-group-flush">
        <div href="#" class="bd bd-y bd-success bd-1 list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1 tx-bold">
            {{ ['A' => __('Air'), 'M' => __('Maritime'), ][$result->type] }}
            </h5>
            <h5 class="tx-bold tx-success"> {{ optional($result->client)->full_name }} / {{ $result->toBranch ? $result->toBranch->code : null }}{{ optional($result->client)->manual_id_dsp }}</h5>
            </div>
            <p class="mb-1">[{{ $result->fromBranch->code }}] {{ $result->fromBranch->name }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                [{{ $result->toBranch->code }}] {{ $result->toBranch->name }}
            </p>
            <small>

            @can('edit-warehouse')
                <a  href="{{ route('tenant.warehouse.edit', [$tenant->domain, $result->id]) }}"><i class="fa fa-pencil-square-o"></i> {{ __('Edit') }} </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endcan

            @if ($result->status == 'A')
                
            <a  href="{{ route('tenant.warehouse.print-sticker', [$tenant->domain, $result->id ]) }}">
                <i class="fa fa-ticket"></i>
                {{ __('Sticker') }}
            </a>

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-invoice')
                <a  href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => optional($result->client)->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-file"></i>
                {{ __('Invoices') }}
                </a>
            @endcan

            &nbsp;&nbsp;&nbsp;&nbsp;

            @can('show-payment')    
            <a  href="{{ route('tenant.payment.list', [$tenant->domain, 'client_id' => optional($result->client)->id, 'branch_id' => $branch->id, ]) }}">
                <i class="fa fa-money"></i>
                {{ __('Payments') }}
            </a>
            @endcan

           @endif
            
            </small>
        </div>
    </div>
@endforeach