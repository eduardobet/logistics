<table class="table mg-b-0 pdf-table">

  <tr>
    <td colspan="2" style="width:34%;"><h5>DETALLES SUCURSAL Y FECHA</h5></td>
    <td colspan="2" style="width:33%;"><h5>INFORMACION DETALLADA</h5></td>
    <td colspan="2" style="width:33%;"><h5>INFORMACION DETALLADA</h5></td>
  </tr>

  <tr>
    <td colspan="2"><h3>{{ request('bname', $branch->name) }}</h3></td>
    <td>Total Facturado:</td>
    <td>{{ $sign }}{{ number_format($tot_charged, 2) }}</td>
    <td>Total Cheques:</td>
    <td>{{ $sign }}{{ number_format($tot_in_check, 2) }}</td>
  </tr>
  
  <tr>
    <td colspan="2">Total clientes: {{ $tot_clients }}</td>
    <td>Total Cobrado:</td>
    <td>{{ $sign }}{{ number_format($tot_income, 2) }}</td>
    <td>Comision Tarjeta:</td>
    <td>{{ $sign }}{{ number_format($tot_commission, 2) }}</td>
  </tr>
  
  <tr>
    <td>Desde:</td>
    <td>{{ request('from', $today = date('d/m/Y')) }}</td>
    <td>Total Efectivo:</td>
    <td>{{ $sign }}{{ number_format($tot_in_cash, 2) }} </td>
    <td>Multa Almacenaje:</td>
    <td>{{ $sign }}{{ number_format($tot_fine, 2) }}</td>
  </tr>

  <tr>
    <td>Hasta:</td>
    <td>{{ request('to', $today) }}</td>
    <td>Total Deposito/Transferencia:</td>
    <td>{{ $sign }}{{ number_format($tot_in_wire, 2) }}</td>
   <td>Libras Ingresadas:</td>
   <td>{{ $recas->sum('weight') }}LBS</td>
  </tr>
  
  
    

    <tr>
      <td colspan="6">
        <h4>DETALLES</h4>
      </td>
    </tr>

    <?php $earningG = 0; $i = 0;?>
            
    @foreach ($payments_by_type->groupBy('invoice_id') as $ptype => $groups)
      <?php
        $total = 0;
        $pending = 0;
        $earning = 0;
      ?>

      @if ($i == 0)
      <tr>
        <th>FACTURA</th>
        <th>FECHA</th>
        <th>T.FAC/METODO</th>
        <th class="tx-center">TOTAL</th>
        <th class="tx-right pdf-a-right">AOBONO/PAGO</th>
        <th class="tx-right pdf-a-right">PENDIENTE</th>
      </tr>
      @endif

      @foreach ($groups as $key => $payment)
        <?php
          if ($pending == 0) $total = $payment->invoice->total;
          else $total = $pending;
          $pending = $total - $payment->amount_paid;
        ?>
        <tr>
          <td>{{ request('bcode', $branch->initial) }}-{{ $payment->invoice->manual_id_dsp }}</td>
          <td>{{ $payment->created_at->format('Y-m-d') }}</td>
          <td>
          @if (isset($payment->invoice->warehouse))
          WH-{{ $payment->invoice->warehouse->manual_id }}
          @else
            {{ __('INTERNET') }}
          @endif
                  / {{ strtoupper( [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check')][ $payment->payment_method ] )  }} 
            </td>
            <td class="tx-center">{{ $sign }}{{ number_format($total, 2) }}</td>
            <td class="tx-right pdf-a-right">{{ $sign }}
              @if (!empty($show_total))
              {{ number_format($payment->amount_paid, 2) }}
              @else
              {{ number_format($payment->amount_paid, 2, ".", "") }}
              @endif
            </td>
            <td class="tx-right pdf-a-right">{{ $sign }}{{ number_format($pending, 2) }}</td>
          </tr>
          <?php 
            $earning += $payment->amount_paid;
            $earningG += $payment->amount_paid;
          ?>
        @endforeach

        @if (!empty($show_total))
        <tr>
          <td colspan="4" class="tx-right pdf-a-right">Total:</td>
          <td class="tx-right pdf-a-right">{{ $sign }}{{ number_format($earning, 2) }}</td>
          <td></td>
        </tr>
        @endif

        @php
            $i++;
        @endphp
      @endforeach


    <tr>
      <td colspan="6">
        <h4>LIBRAS INGRESADAS</h4>
      </td>
    </tr>

    <tr>
      <th>FECHA</th>
      <th colspan="2">RECA</th>
      <th>Cant Paquetes</th>
      <th colspan="2" class="tx-right pdf-a-right">TOTAL LIBRAS</th>
    </tr>
    @foreach ($recas as $reca)
      <tr>
        <td>{{ $reca->created_at->format('d/m/Y') }}</td>
        <td colspan="2">RECA-{{ $reca->id }}</td>
        <td class="tx-center pdf-a-center">{{ count(explode(PHP_EOL, $reca->trackings)) }}</td>
        <td colspan="2" class="tx-right pdf-a-right">{{ $reca->weight }}LBS</td>
      </tr>
    @endforeach

</table>