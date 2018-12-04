<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Invoice') }}</title>
    <style>
    .page-break {page-break-after: always;}
    </style>
  </head>
  <body>
    <table style="width: 100%" cellspacing="0">

        <tr>
            <td style="width:100%;" colspan="2">
                <?php $logo = storage_path('app/public/'.($branch->logo ? $branch->logo : $tenant->logo)); $mime = @mime_content_type($logo);  ?>

                @if ($mime)
                <img src="data:{{ $mime }};base64,{{ base64_encode(file_get_contents( $logo )) }}" alt="Company logo" width="200px"   />
                @endif
            </td>
        </tr>

        <tr><td colspan="2" style="text-align:center">&nbsp;
        @if (request('html'))
            <button type="button" id="print-invoice">{{ __('Print :what', ['what' => __('Invoice') ]) }}</button>
        @endif    
        </td></tr>

        <tr>
            <td style="width:50%; padding-left:10px; vertical-align: top; border: solid 1px">
                <h3>{{ __('Company') }}:</h3>
                {{ $ibranch->name }} <br>
                {{ $ibranch->address }} <br>
                {{ __('TAX ID') }}: {{ $ibranch->ruc }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __('DV') }} {{ $ibranch->dv }} <br>
                {{ $ibranch->telephones }}<br>
            </td>

            <td style="width:50%; padding-left:10px; vertical-align: top; border: solid 1px">
                <h3>{{ __('Client') }}:</h3>
                <div style="padding-left:10px;">
                    {{ $client->full_name }} / {{ $box }} <br>
                    {{ __('Telephones') }}: {{ $client->telephones }} <br>
                    <h3>{{ __('Invoice') }}#: {{ $ibranch->initial }}-{{ $invoice->id }} 
                        @if ($invoice->warehouse_id)
                        &nbsp;&nbsp; /&nbsp;&nbsp; <a href="{{ route('tenant.warehouse.edit', [$tenant->domain, $invoice->warehouse_id, ]) }}" target="_blank">{{ __('Warehouse') }}#: {{ $invoice->warehouse_id }}</a>
                        
                        @endif
                    <br>
                    {{ __('Invoice date') }}: {{ $invoice->created_at->format('d-m-Y') }}
                    </h3>
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="vertical-align: top; border: solid 1px">
                <table style="width: 100%;" cellspacing="0">
                    
                    @if (!$invoice->warehouse_id)
                        <tr>
                            <th style="border: solid 1px;">{{ __('Qty') }}</th>
                            <th style="border: solid 1px;">{{ __('Type') }}</th>
                            <th style="border: solid 1px;">{{ __('Description') }}</th>
                            <th style="border: solid 1px;">{{ __('Purchase ID') }}</th>
                            <th style="border: solid 1px;text-align:right;">{{ __('Sub Total') }}</th>
                        </tr>
                    @else
                        <tr>
                            <th style="border: solid 1px;">{{ __('Type') }}</th>
                            <th style="border: solid 1px;">{{ __('Length') }}</th>
                            <th style="border: solid 1px;">{{ __('Width') }}</th>
                            <th style="border: solid 1px;">{{ __('Height') }}</th>
                            <th style="border: solid 1px;">{{ __('Qty') }}</th>
                            <th style="border: solid 1px;">{{ __('Price') }}</th>
                        </tr>
                    @endif
                    
                    @foreach ($invoice->details as $detail)
                        @if (!$invoice->warehouse_id)    
                            <tr>
                                <td style="border: solid 1px;">{{ $detail->qty }}</td>
                                <td style="border: solid 1px;">{{ [1 => __('Online shopping'), 2 => __('Card commission'), ][$detail->type] }}</td>
                                <td style="border: solid 1px;">{{ $detail->description }}</td>
                                <td style="border: solid 1px;">{{ $detail->id_remote_store }}</td>
                                <td style="border: solid 1px; text-align:right;">${{ number_format($detail->total, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td style="border: solid 1px;">{{ [1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', ][$detail->type] }}</td>
                                <td style="border: solid 1px;">{{ $detail->length }}</td>
                                <td style="border: solid 1px;">{{ $detail->width }}</td>
                                <td style="border: solid 1px;">{{ $detail->height }}</td>
                                <td style="border: solid 1px;">
                                    @if ($invoice->i_using == 'R')
                                        {{ $detail->real_weight }}
                                    @elseif($invoice->i_using == 'V')
                                        {{ $detail->vol_weight }}
                                    @else
                                        {{ $detail->cubic_feet }}      
                                    @endif
                                </td>
                                <td style="border: solid 1px;">
                                    @if ($invoice->i_using == 'R')
                                        {{ $detail->real_price }}
                                    @elseif($invoice->i_using == 'V')
                                        {{ $detail->vol_price }}
                                    @else
                                        {{ $detail->cubic_price }}      
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    <tr style="font-weight: bold">
                        <td colspan="{{ $invoice->warehouse_id ? 5 : 4 }}" style="border: solid 1px;text-align:right;">{{ __('Total') }}:</td>
                        <td style="border: solid 1px;text-align:right;">${{ number_format($invoice->total, 2)  }}</td>
                    </tr>
                    <tr style="font-weight: bold">
                        <td colspan="{{ $invoice->warehouse_id ? 5 : 4 }}" style="border: solid 1px;text-align:right;">{{ __('Amount paid') }}:</td>
                        <td style="border: solid 1px;text-align:right;">${{ number_format($amountPaid, 2)  }}</td>
                    </tr>
                    <tr style="font-weight: bold">
                        <td colspan="{{ $invoice->warehouse_id ? 5 : 4 }}" style="border: solid 1px;text-align:right;">{{ __('Pending') }}:</td>
                        <td style="border: solid 1px;text-align:right;">${{ number_format($invoice->total-$amountPaid, 2)  }}</td>
                    </tr>
                    
                </table>
            </td>
        </tr>

        @if ($invoice->warehouse_id)  
        <tr>
            <td style="" colspan="2">
                <br>
                {{ __('Tracking numbers') }}<br>
                {!! $invoice->warehouse->trackings !!}
            </td>
        </tr>
        @endif

        <tr>
            <td style="border-top: 1px solid #000; text-align:center" colspan="2">
                <br>
            
                <?php
                    echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("{$invoice->id}", "C39", 2, 60, [0,0,0], true) . '" alt="barcode"   />';
                ?>
            </td>
       </tr>

        <tr>
            <td style="" colspan="2">
                <br>
                &nbsp;&nbsp;&nbsp;{{ $creatorName }}
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <h3>{{ __('Terms and Conditions') }}</h3>
    <p>
        {{ $tenant->conditionsInvoice->first()->content }}
    </p>
    @if (request('html'))
    <script>
        var $btnPrintInvoice = document.querySelector('#print-invoice');
        if ($btnPrintInvoice) {
            $btnPrintInvoice.addEventListener('click', function(e) {
                window.open("{{ route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id, ]) }}",'_blank');
                e.preventDefault();
            });
        }
    </script>
    @endif
  </body>
</html>