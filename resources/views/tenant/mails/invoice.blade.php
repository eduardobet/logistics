<strong>#{{ $invoice->id }}</strong><br>
{{ $ibranch->name }}<br>
{{ __('TAX ID') }} {{ $ibranch->ruc }} {{ __('DV') }} {{ $ibranch->dv }} <br>
{{ $ibranch->address }} <br>
{{ $ibranch->telephones }} <br><br>
{{ $client->full_name }} / {{ $box }} <br>
{{ __('Telephones') }}: {{ $client->telephones }}<br><br>

{{ __('Invoice date') }}: {{ $invoice->created_at->format('d-m-Y') }}

<table>
    <tr>
        <th>{{ __('Qty') }}</th>
        <th>{{ __('Type') }}</th>
        <th>{{ __('Description') }}</th>
        <th>{{ __('Purchase ID') }}</th>
        <th>{{ __('Total') }}</th>
    </tr>

    
    @foreach ($invoice->details as $detail)
        <tr>
            <td>{{ $detail->qty }}</td>
            <td>{{ [1 => __('Online shopping'), 2 => __('Card commission'), ][$detail->type] }}</td>
            <td>{{ $detail->description }}</td>
            <td>{{ $detail->id_remote_store }}</td>
            <td>${{ number_format($detail->total, 2) }}</td>
        </tr>
    @endforeach
    
</table>

<br><br>
<strong>
{{ __('Total') }}: ${{ number_format($invoice->total, 2) }} <br>
{{ __('Amount paid') }}: ${{ number_format($payments->sum('amount_paid'), 2) }} <br>
{{ __('Pending') }}: ${{ number_format($invoice->total - $payments->sum('amount_paid'), 2) }} <br> <br>

<em>{{ $creatorName }}</em>
</strong>
