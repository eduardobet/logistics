<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Incomes') }}</title>
    <style>
       table.pdf-table{width: 100% !important;}
       table.pdf-table td.pdf-a-right, th.pdf-a-right{text-align: right !important;}
       table.pdf-table td.pdf-a-center, th.pdf-a-center{text-align: center !important;}
       table.pdf-table td.pdf-mt-5{padding-top: 20px}
       table.pdf-table th{text-align: left}
       table.pdf-table td{border-bottom: solid 1px}
       table.pdf-table th{border: solid 1px}
    </style>
  </head>
  <body>

  <table style="width: 100%">

        <tr>
            <td style="width:70%;">
                <?php $logo = storage_path('app/public/'.($branch->logo ? $branch->logo : $tenant->logo)); $mime = @mime_content_type($logo);  ?>

                @if ($mime)
                <img src="data:{{ $mime }};base64,{{ base64_encode(file_get_contents( $logo )) }}" alt="Company logo" width="200px"  height="80px"  />
                @endif
            </td>

            <td style="width: 30%; border:solid 1px; vertical-align: top; text-align: center;">
                <h3>{{ __('Incomes') }}</h3>
                {{ request('from') }} - {{ request('to') }}
            </td>


        </tr>

    </table>

    <p></p>

@include('tenant.income._index')

</body>
</html>