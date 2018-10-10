@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Invoice', [], $lang) }} #{{ $ibranch->initial }}-{{ $invoice->id }}
        @endcomponent
@endslot

#{{ $ibranch->name }}<br>
{{ __('TAX ID', [], $lang) }} {{ $ibranch->ruc }} {{ __('DV', [], $lang) }} {{ $ibranch->dv }} <br>
{{ $ibranch->address }} <br>
{{ $ibranch->telephones }} <br><br>
#{{ $client->full_name }} / {{ $box }} <br>
{{ __('Telephones', [], $lang) }}: {{ $client->telephones }}<br><br>

{{ __('Invoice date', [], $lang) }}: {{ $invoice->created_at->format('d-m-Y') }}

<table style="width: 100%" cellspacing="0">
    @if (!$invoice->warehouse_id)
        <tr>
            <th style="border: solid 1px;">{{ __('Qty', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Type', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Description', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Purchase ID', [], $lang) }}</th>
            <th style="border: solid 1px;text-align:right;">{{ __('Total', [], $lang) }}</th>
        </tr>
     @else
        <tr>
            <th style="border: solid 1px;">{{ __('Qty', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Type', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Length', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Width', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('Height', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('P/Vol', [], $lang) }}</th>
            <th style="border: solid 1px;">{{ __('P/Real', [], $lang) }}</th>
            <th style="border: solid 1px;">DHL?</th>
        </tr>
     @endif
    
    @foreach ($invoice->details as $detail)
        @if (!$invoice->warehouse_id)
            <tr>
                <td style="border: solid 1px;">{{ $detail->qty }}</td>
                <td style="border: solid 1px;">{{ [1 => __('Online shopping', [], $lang), 2 => __('Card commission', [], $lang), ][$detail->type] }}</td>
                <td style="border: solid 1px;">{{ $detail->description }}</td>
                <td style="border: solid 1px;">{{ $detail->id_remote_store }}</td>
                <td style="border: solid 1px;text-align:right;">${{ number_format($detail->total, 2) }}</td>
            </tr>
        @else
            <tr>
                <td style="border: solid 1px;">{{ $detail->qty }}</td>
                <td style="border: solid 1px;">{{ [1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', ][$detail->type] }}</td>
                <td style="border: solid 1px;">{{ $detail->length }}</td>
                <td style="border: solid 1px;">{{ $detail->width }}</td>
                <td style="border: solid 1px;">{{ $detail->height }}</td>
                <td style="border: solid 1px;">{{ $detail->vol_weight }}</td>
                <td style="border: solid 1px;">{{ $detail->real_weight }}</td>
                <td style="border: solid 1px;">{{ $detail->is_dhll ? 'DHL' : null }}</td>
            </tr>
        @endif    
    
    @endforeach
    
</table>

@if ($invoice->warehouse_id)  
<br><br>
{{ __('Tracking numbers', [], $lang) }} <br>
{!! $invoice->warehouse->trackings !!}
@endif

<br><br>
<strong>
{{ __('Total', [], $lang) }}: ${{ number_format($invoice->total, 2) }} <br>
{{ __('Amount paid', [], $lang) }}: ${{ number_format($payments->sum('amount_paid'), 2) }} <br>
{{ __('Pending', [], $lang) }}: ${{ number_format($invoice->total - $payments->sum('amount_paid'), 2) }} <br> <br>

<em>{{ $creatorName }}</em>
</strong>


@isset($subcopy)
    @slot('subcopy')
        @component('mail::subcopy')
            {!! $subcopy !!}
        @endcomponent
    @endslot
@endisset

@slot('footer')
    @component('mail::footer')
        &copy; {{ date('Y') }} {{ config('app.name') }}
    @endcomponent
@endslot

@endcomponent