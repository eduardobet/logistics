<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Invoices') }}</title>
    <style>
       table.pdf-table{width: 100% !important; border-collapse: collapse;}
       table.pdf-table td.pdf-a-right, th.pdf-a-right{text-align: right !important;}
       table.pdf-table td.pdf-mt-5{padding-top: 20px}
       table.pdf-table th{text-align: left}
       table.pdf-table td{border-bottom: solid 1px}
       table.pdf-table th{border: solid 1px}

       .page-break {page-break-after: always;}
       #header,
#footer {
  position: fixed;
  left: 0;
	right: 0;
	color: #aaa;
	font-size: 0.9em;
}
#header {
  top: 0;
	border-bottom: 0.1pt solid #aaa;
}
#footer {
  bottom: 0;
  border-top: 0.1pt solid #aaa;
}
.page-number:before {
  content: "{{ __('Page') }} " counter(page);
}
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

        <tr>
            <td style="width: 70%; border:solid 1px; vertical-align: top;">
                 {{ $branch->name }} <br>
                 {{ $branch->address }} <br>
                 {{ __('TAX ID') }}: {{ $branch->ruc }} {{ __('DV') }} {{ $branch->dv }} <br>
                 {{ $branch->telephones }}<br>
            </td>

            <td style="width: 30%; border:solid 1px; vertical-align: top; text-align: right; padding-right: 50px">
                <h3>{{ __('Invoices') }}</h3>
                {{ request('from') }} - {{ request('to') }}
            </td>
        </tr>
    </table>

    <p></p>

@include('tenant.invoice._index', ['invoices' => $invoices])

</body>
</html>