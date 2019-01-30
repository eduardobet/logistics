@if (isset($printing))
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
        <link href="{{ asset(mix('css/tenant.css')) }}" rel="stylesheet">
    </head>
    <body>
 @endif

<div class="card card-invoice">
          <div class="card-body">
            <div class="invoice-header">
              <h1 class="invoice-title">REPORTE, {{ date('d-m-Y') }}</h1>

            </div><!-- invoice-header -->

            <div class="row mg-t-20">
              <div class="col-md">
                <label class="section-label-sm tx-gray-500">Detalles Sucursal y Fecha</label>
                <div class="billed-to">
                  <h6 class="tx-gray-800">{{ request('bname', $branch->name) }}</h6>
                  <p><b>FECHA:</b> <br>Desde: {{ request('from', $today = date('d/m/Y')) }}<br> Hasta: {{  request('to', $today) }}<br>
                </div>
              </div><!-- col -->
              <div class="col-md">
                <label class="section-label-sm tx-gray-500">Informacion Detallada</label>
                <p class="invoice-info-row">
                  <span>Total Facturado</span>
                  <span>${{ number_format($tot_charged, 2) }}</span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Cobrado</span>
                  <span>${{ number_format($tot_income, 2) }}</span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Efectivo:</span>
                  <span>${{ number_format($tot_in_cash, 2) }} </span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Deposito/Transferencia:</span>
                  <span>${{ number_format($tot_in_wire, 2) }}</span>
                </p>

              </div><div class="col-md">
                <label class="section-label-sm tx-gray-500">Informacion Detallada</label>
                <p class="invoice-info-row">
                  <span>Total Cheques:</span>
                  <span>${{ number_format($tot_in_check, 2) }}</span>
                </p>
                <p class="invoice-info-row">
                  <span>Comision Tarjeta:</span>
                  <span>${{ number_format($tot_commission, 2) }}</span>
                </p>
                <p class="invoice-info-row">
                  <span>Multa Almacenaje:</span>
                  <span>${{ number_format($tot_fine, 2) }}</span>
                </p>
                <p class="invoice-info-row">
                  <span>Libras Ingresadas:</span>
                  <span>{{ $recas->sum('weight') }}LBS</span>
                </p>

              </div><!-- col -->
            </div><!-- row -->


                
                @foreach ($payments_by_type->groupBy('payment_method') as $ptype => $groups)
                <div class="table-responsive mg-t-40 table-bordered">
                  <table class="table table-invoice" >
                    <thead class="thead-colored {{[1 => 'bg-primary', 2 => 'bg-success', 3 => 'bg-danger'][$ptype]}}">
                      <tr>
                        <th class="wd-10p">{{ strtoupper( [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check')][$ptype] )  }}</th>
                      </tr>
                    </thead>
                  </table>
                  <table class="table table-invoice" >
                    <thead>
                      <tr>
                        <th class="wd-10p">FACTURA</th>
                        <th class="wd-15p">FECHA</th>
                        <th class="wd-35p">TIPO DE FACTURA</th>
                        <th class="wd-10p tx-center">TOTAL</th>
                        <th class="wd-10p tx-right">AOBONO/PAGO</th>
                        <th class="wd-10p tx-right">PENDIENTE</th>
                      </tr>
                    </thead>
                    <tbody class="mg-b-0">
                      @foreach ($groups as $payment)
                      <tr>
                        <td>{{ request('bcode', $branch->branch_code) }}-{{ $payment->invoice_id }}</td>
                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td>
                        
                        @if ($wId = $payment->invoice->warehouse_id)
                        WH-{{ $wId }}
                        @else
                          {{ __('INTERNET') }}
                        @endif
                        
                        </td>
                        <td class="tx-center">${{ number_format($payment->invoice->total, 2) }}</td>
                        <td class="tx-right">${{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="tx-right">${{ number_format($payment->invoice->total - $payment->amount_paid, 2) }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
            </div><!-- table-responsive -->
                @endforeach
            

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice" >
                <thead class="thead-colored bg-info">
                  <tr>
                    <th class="wd-10p ">LIBRAS INGRESADAS</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-10p">RECA</th>
                    <th class="wd-20p">Cant Paquetes</th>
                    <th class="wd-60p tx-right">TOTAL LIBRAS</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  @foreach ($recas as $reca)
                    <tr>
                      <td>{{ $reca->created_at->format('d/m/Y') }}</td>
                      <td>RECA-{{ $reca->id }}</td>
                      <td>{{ count(explode(PHP_EOL, $reca->trackings)) }}</td>
                      <td class="tx-right">{{ $reca->weight }}LBS</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div><!-- table-responsive -->

          </div><!-- card-body -->
        </div><!-- card -->
@if (isset($printing))
</body>
</html>
@endif