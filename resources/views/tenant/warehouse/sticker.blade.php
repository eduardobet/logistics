<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Sticker') }}</title>
    <style>
        body {
          width: 384px; height: 576px;
        }
    </style>
  </head>
  <body>
    
    <div id="content">
      <table style="width: 384px; height: 576px;" cellspacing="0" id="sticker">
      <tr>
        <td style="border-top: 1px solid #000;margin-left: 10px" colspan="2">
            &nbsp;&nbsp;&nbsp;{{  __('Shipper') }} <br>
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
            @if ($warehouse->tot_packages && $warehouse->tot_weight)
                {{ number_format($warehouse->tot_weight, 2) }} LBS &nbsp;&nbsp;&nbsp;&nbsp; 0x0x0 &nbsp;&nbsp;&nbsp;&nbsp; ({{ $warehouse->tot_packages }})<br>
            @endif
            
            @if ($client)
            {{ $client->branch->code }}{{ $client->manual_id_dsp }} / {{ $client->full_name }}      ${{ number_format($invoice ? $invoice->total : 0, 2) }}
            @endif
        </td>
      </tr>

      <tr>
        <td style="text-align:center;border-top: 1px solid #000; font-size: 3em; font-weight: bold; " colspan="2">
            {{ $warehouse->manual_id_dsp }}
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000; border-right: 1px solid #000;">
            &nbsp;&nbsp;&nbsp;{{  __('Destination') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size: 2em; font-weight: bold;">{{ $iata }}</span>
        </td>
        <td style="border-top: 1px solid #000;">
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
                echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("{$warehouse->manual_id_dsp}", "C39", 2, 90, [0,0,0], true) . '" alt="barcode"   />';
            ?>
        </td>
      </tr>

      <tr>
        <td style="" colspan="2">
            <br>
            &nbsp;&nbsp;&nbsp;{!! $user->full_name !!}
        </td>
      </tr>


    </table>
    </div>

    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.js"></script>

    <script>
      
    html2canvas(document.getElementById("content"), {
      scale: 2,
    }).then(function(canvas){
        var img = canvas.toDataURL("image/png");
        var doc = new jsPDF('p', 'px', [288.18, 432]);
        //var doc = new jsPDF();
        doc.addImage(img,'PNG',0,0);
        doc.autoPrint()
       // doc.save('sticker_' + (new Date().getTime()).toString(16) +'.pdf');
        window.open(doc.output('bloburl'), '_blank');
     });
    </script>
  </body>
</html>