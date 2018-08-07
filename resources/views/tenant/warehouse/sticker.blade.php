<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Sticker') }}</title>
    <style>
        body {
            margin: 10%;
        }
    </style>
  </head>
  <body>
    <table style="width: 100%" cellspacing="0">
      <tr>
        <td style="border-top: 1px solid #000;margin-left: 10px" colspan="2">
            &nbsp;&nbsp;&nbsp;{{  __('Shiper') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $mailer ? $mailer->name : null }}
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000;margin-left: 10px" colspan="2">
            &nbsp;&nbsp;&nbsp;{{  __('Consignee') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $branchTo->name }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $branchTo->address }}
        </td>
      </tr>

      <tr>
        <td style="text-align:center;border-top: 1px solid #000;" colspan="2">
            @foreach ($invoice ? $invoice->details : [] as $detail)
                {{ number_format($detail->vol_weight, 2) }}LBS &nbsp;&nbsp;&nbsp;&nbsp; {{ $detail->length }}x{{ $detail->width }}x{{ $detail->height }} &nbsp;&nbsp;&nbsp;&nbsp; ({{ $detail->qty }}) <br>
            @endforeach
            {{ $client->boxes->first()->branch_code }}{{ $client->id }} / {{ $client->full_name }}      ${{ number_format($invoice ? $invoice->total : 0, 2) }}
        </td>
      </tr>

      <tr>
        <td style="text-align:center;border-top: 1px solid #000; font-size: 3em; font-weight: bold; " colspan="2">
            {{ $warehouse->id }}
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000; border-right: 1px solid #000;" width="20%">
            &nbsp;&nbsp;&nbsp;{{  __('Destination') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size: 2em; font-weight: bold;">{{ $iata }}</span>
        </td>
        <td style="border-top: 1px solid #000;" width="80%">
            &nbsp;{{  __('Route') }} <br>
            <span style="font-size: 2em; font-weight: bold;">&nbsp;&nbsp;&nbsp;</span>
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000">
            &nbsp;&nbsp;&nbsp;{{  __('Service No') }} <br>
            <span style="font-size: 2em; font-weight: bold;">&nbsp;&nbsp;&nbsp;</span>
        </td>

        <td style="border-top: 1px solid #000">
            &nbsp;&nbsp;&nbsp;{{ $warehouse->created_at->format('d/m/Y') }} <br>
            &nbsp;&nbsp;&nbsp;<span style="font-size: 2em; font-weight: bold;">
                {{ strtoupper( ['A' => __('Air'), 'M' => __('Maritime'), ][$warehouse->type] ) }}
            </span>
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000; text-align:center" colspan="2">
            <br>
        
            <?php
                echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("{$warehouse->id}", "C39", 2, 90, [0,0,0], true) . '" alt="barcode"   />';
            ?>
        </td>
      </tr>

      <tr>
        <td style="" colspan="2">
            <br>
            &nbsp;&nbsp;&nbsp;{{ $user->full_name }}
        </td>
      </tr>


    </table>
  </body>
</html>