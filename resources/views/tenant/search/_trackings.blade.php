@foreach ($results as $key => $resultsGroup)
    
    @if ($key == 'cargo_entries')
        
        @foreach ($resultsGroup as $result)
            <div class="list-group list-group-flush">
                <div href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">
                        RECA-{{ $result->id }}
                    </h5>
                    </div>
                    <p class="mb-1">[{{ $result->branch->code }}] {{ $result->branch->name }}</p>
                    <p class="mb-1">{{ __('By') }}: {{ $result->creator->full_name }}</p>
                    <p class="mb-1">{{ $result->created_at->format('d/m/Y H:i a') }}</p>

                    <p class="mb-1">
                        @if (!$result->type || $result->type == 'N' )
                            <label class="badge badge-success">
                                {{ __('Normal') }}
                            </label>
                        @elseif($result->type == 'M')
                            
                            <label class="badge badge-danger">
                                {{ __('Misidentified') }}
                            </label>
                        @endif
                    </p>

                    @can('edit-warehouse')
                        <a  href="{{ route('tenant.warehouse.cargo-entry.show', [$tenant->domain, $result->id]) }}"><i class="fa fa-eye"></i> {{ __('Show') }} </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    @endcan

                    </small>
                </div>
            </div>
        @endforeach
        
    @endif

    @if ($key == 'warehouses')
        @foreach ($resultsGroup as $result)
            <div class="list-group list-group-flush">
                <div href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">
                        WH-{{ $result->manual_id }}
                    </h5>
                    </div>
                    <p class="mb-1">[{{ $result->toBranch->code }}] {{ $result->toBranch->name }}</p>
                    <p class="mb-1">{{ __('By') }}: {{ $result->creator->full_name }}</p>
                    <p class="mb-1">{{ $result->created_at->format('d/m/Y H:i a') }}</p>

                    <small>

                        @can('edit-warehouse')
                            <a  href="{{ route('tenant.warehouse.edit', [$tenant->domain, $result->id]) }}"><i class="fa fa-pencil-square-o"></i> {{ __('Edit') }} </a>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            
                            <a  href="{{ route('tenant.warehouse.print-sticker', [$tenant->domain, $result->id ]) }}">
                                <i class="fa fa-ticket"></i>
                                {{ __('Sticker') }}
                            </a>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        @endcan


                        @can('show-invoice')
                            <a  href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => $result->client->id, 'branch_id' => $branch->id, ]) }}">
                            <i class="fa fa-file"></i>
                            {{ __('Invoices') }}
                            </a>
                        @endcan

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        @can('show-payment')    
                        <a  href="{{ route('tenant.payment.list', [$tenant->domain, 'client_id' => $result->client->id, 'branch_id' => $branch->id, ]) }}">
                            <i class="fa fa-money"></i>
                            {{ __('Payments') }}
                        </a>
                        @endcan
                        
                    </small>
                </div>
            </div>
        @endforeach
        
    @endif
    
@endforeach