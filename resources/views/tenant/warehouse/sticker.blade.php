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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MAILER NAME
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000;margin-left: 10px" colspan="2">
            &nbsp;&nbsp;&nbsp;{{  __('Consignee') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BRANCH NAME <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BRANCH ADDRESS
        </td>
      </tr>

      <tr>
        <td style="text-align:center;border-top: 1px solid #000;" colspan="2">
            21LBS &nbsp;&nbsp;&nbsp;&nbsp; 12x12x12 &nbsp;&nbsp;&nbsp;&nbsp; (2) <br>
            PRLAXXX / The Hollow Man &nbsp;&nbsp;&nbsp;&nbsp; $0.00
        </td>
      </tr>

      <tr>
        <td style="text-align:center;border-top: 1px solid #000; font-size: 3em; font-weight: bold; " colspan="2">
            00000000000
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000; border-right: 1px solid #000;" width="20%">
            &nbsp;&nbsp;&nbsp;{{  __('Destination') }} <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size: 2em; font-weight: bold;">PTY</span>
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
            &nbsp;&nbsp;&nbsp;00/00/0000 <br>
            &nbsp;&nbsp;&nbsp;<span style="font-size: 2em; font-weight: bold;">AIR</span>
        </td>
      </tr>

      <tr>
        <td style="border-top: 1px solid #000; text-align:center" colspan="2">
            <br>
        
            <?php
                echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("00000000000", "C39", 2, 90, [0,0,0], true) . '" alt="barcode"   />';
            ?>
        </td>
      </tr>

      <tr>
        <td style="" colspan="2">
            <br>
            &nbsp;&nbsp;&nbsp;User Name
        </td>
      </tr>


    </table>
  </body>
</html>