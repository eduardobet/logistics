<table class="table table-hover mg-b-0 pdf-table">
    <thead>
    <tr>
        <th>{{ __('ID') }}</th>
        <th>{{ __('Type') }}</th>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Issuer branch') }}</th>
        <th>{{ __('Destination branch') }}</th>
        <th>{{ __('Status') }}</th>

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
        
        @foreach ($warehouses->groupBy('type') as $key => $groups)
            <tr>
                <td colspan="5" class="pdf-mt-5">
                    <b>{{ ['A' => __('Air'), 'M' => __('Maritime'), ][$key] }}: {{ $groups->count() }}</b>
                </td>

                @if (!isset($exporting))
                    <td></td>
                @endif
            </tr>

            @foreach ($groups as $warehouse)
            <tr>
                <th scope="row">{{ $warehouse->manual_id_dsp }}</th>
                <td>{{ ['A' => __('Air'), 'M' => __('Maritime')][$warehouse->type] }}</td>
                <td>{{ $warehouse->created_at->format('d/m/Y') }}</td>
                <td>{{ $warehouse->fromBranch->name }}</td>
                <td>{{ $warehouse->toBranch->name }}</td>
                <td>{{ $warehouse->status }}</td>
                @if (!isset($exporting))
                <td class="text-center">
                    @can('edit-warehouse')
                    <a  title="{{ __('Edit') }}" href="{{ route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]) }}"><i class="fa fa-pencil-square-o"></i></a>
                    @endcan
                </td>
                @endif
            </tr>

            @endforeach
        @endforeach

    </tbody>
</table>