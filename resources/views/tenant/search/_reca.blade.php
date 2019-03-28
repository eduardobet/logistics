@foreach ($results as $result)
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
            <p class="mb-1">{{ $result->weight }} (LBS)</p>
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
            @endcan
        </div>
    </div>
@endforeach