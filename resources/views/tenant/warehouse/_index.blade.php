<table class="table table-hover mg-b-0 pdf-table">
    <thead>
    <tr>
        <th>{{ __('ID') }}</th>
        <th>{{ __('Type') }}</th>
        <th>{{ __('Issuer branch') }}</th>
        <th>{{ __('Destination branch') }}</th>

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
        
        @foreach ($warehouses as $warehouse)
        <tr>
            <th scope="row">{{ $warehouse->id }}</th>
            <td>{{ ['A' => __('Air'), 'M' => __('Maritime')][$warehouse->type] }}</td>
            <td>{{ $warehouse->fromBranch->name }}</td>
            <td>{{ $warehouse->toBranch->name }}</td>
            @if (!isset($exporting))
            <td class="text-center">
                @can('edit-warehouse')
                <a title="{{ __('Edit') }}" href="{{ route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]) }}"><i class="fa fa-pencil-square-o"></i></a>
                @endcan
            </td>
            @endif
        </tr>

    @endforeach

    </tbody>
</table>