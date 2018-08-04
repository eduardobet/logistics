<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Payments') }}</title>
    <style>
        body {
            margin: 10%;
        }
       table.pdf-table{width: 100% !important; border-collapse: collapse;}

       .page-break {page-break-after: always;}
    </style>
  </head>
  <body>

  <table style="width: 100%" cellspacing="0">

        <tr>
            <td style="width:100%; border: solid 1px" colspan="2">
                <?php $logo = storage_path('app/public/'.$tenant->logo); $mime = @mime_content_type($logo);  ?>

                @if ($mime)
                <img src="data:{{ $mime }};base64,{{ base64_encode(file_get_contents( $logo )) }}" alt="Company logo" width="200px"   />
                @endif
            </td>
        </tr>

        <tr>
            <td style="width: 50%; border:solid 1px">
                 {{ $ibranch->name }} <br>
                 {{ $ibranch->address }} <br>
                 {{ __('TAX ID') }}: {{ $ibranch->ruc }} {{ __('DV') }} {{ $ibranch->dv }} <br>
                 {{ $ibranch->telephones }}<br>
            </td>

            <td style="width: 50%; border:solid 1px">
            
            </td>
        </tr>
    </table>

@include('tenant.payment._index', ['payments' => $payments])

</body>
</html>