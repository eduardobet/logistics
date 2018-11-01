@extends('layouts.tenant')

@section('content')

<!-- slim-mainpanel -->
<div class="slim-mainpanel">
    <div class="container">
      <div class="slim-pageheader">
        <ol class="breadcrumb slim-breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('tenant.invoice.list', [$tenant->domain, 'client_id' => request('client_id'), 'branch_id' => request('branch_id'), ]) }}">{{ __('Invoices') }}</a></li>
          <li class="breadcrumb-item">{{ __('Showing :what', ['what' => __('Invoice') ]) }}</li>
        </ol>
        <h6 class="slim-pagetitle">{{ __('Invoice details') }}</h6>
      </div>
      <!-- slim-pageheader -->
      <div class="card card-invoice">
                <div class="card-body">
                  <div class="invoice-header">
                    <h1 class="invoice-title">{{ __('Invoice') }}</h1>
                    <div class="billed-from">
                      <h6>{{ __('Branch') }}.</h6>
                      <p>{{ $invoice->branch->address }}<br>
                      Tel: {{ $invoice->branch->telephones }}<br>
                      Email: {{ $invoice->branch->emails }}</p>
                    </div><!-- billed-from -->
                  </div><!-- invoice-header -->

                  <div class="row mg-t-20">
                    <div class="col-md">
                      <label class="section-label-sm tx-gray-500">{{ __('Invoiced to') }}.</label>
                      <div class="billed-to">
                        <h6 class="tx-gray-800">{{ $invoice->client->full_name }} / {{ $invoice->client->boxes->first()->branch_code }}
                            {{ $invoice->client->id }}
                        </h6>
                        <p> {{ __('PID') }}: {{ $invoice->client->pid }}<br>
                        Email: {{ $invoice->client->email }}</p>
                      </div>
                    </div><!-- col -->
                    <div class="col-md">
                      <label class="section-label-sm tx-gray-500">{{ __('Invoice info') }}</label>
                      <p class="invoice-info-row">
                        <span>{{ __('Number') }}</span>
                        <span>{{ $invoice->branch->initial }}-{{ $invoice->id }}</span>
                      </p>
                      <p class="invoice-info-row">
                        <span>{{ __('Warehouse') }}</span>
                        <span>
                            @if ($invoice->warehouse_id)
                                <a href="{{ route('tenant.warehouse.edit', [$tenant->domain, $invoice->warehouse_id]) }}">WH-{{ $invoice->warehouse_id }}</a>
                            @else
                                <a href="#">WH-0</a>   
                            @endif
                        
                        </span> <!--en caso de ser factura de compra ese campo muestra un 0 , el enlace debe ir al warehouse asignado a esta factura-->
                      </p>
                      <p class="invoice-info-row">
                        <span>{{ __('Created at') }}:</span>
                        <span>{{ $invoice->created_at->format('d/m/Y') }} </span>
                      </p>
                      <p class="invoice-info-row">
                        <span>{{ __('Due date') }}:</span> <!--7 dias habiles a la fecha de creacion -->
                        <span>{{ $invoice->created_at->addDays(7)->format('d/m/Y') }}</span>
                      </p>
                    </div><!-- col -->
                  </div><!-- row -->

                  

                  <div class="table-responsive mg-t-40">
                    <table class="table table-invoice">
                      <thead>
                        <tr>
                          <th class="wd-20p">{{ __('Type') }}</th> <!--Compra por internet / Encomienda / Comision Tarjeta-->
                          <th class="wd-40p">{{ __('Description') }}</th><!-- Una Compra en (sitio web) / Un Flete Aereo. Maritimo en caso de warehouse-->
                          <th class="tx-center">{{ __('Qty') }}</th> <!--cantidad de libras a facturar-->
                          <th class="tx-right">{{ __('Price') }}</th> <!--en este caso deberia aplicar aca 2,50 o el precio estipulado de las libras-->
                          <th class="tx-right">{{ __('Amount') }}</th>
                        </tr>
                      </thead>
                      <tbody>

                        @foreach ($invoice->details as $detail)
                            <tr>
                                <td>{{ [1=> __('Online shopping'), 2=> __('Card commission'), 3 => __('Direct comission') ][$detail->type] }}</td>
                                <td class="tx-12">{{ $detail->description }}</td>
                                <td class="tx-center">{{ $detail->qty }}</td>
                                <td class="tx-right">${{ number_format( $detail->total, 2 ) }}</td>
                                <td class="tx-right">${{ number_format( $detail->qty * $detail->total, 2 ) }}</td>
                            </tr>
                        @endforeach


                        {{--
                        <tr>
                          <td>Servicio de Encomienda</td>
                          <td class="tx-12">Un Flete Aereo MIA - PTY</td>
                          <td class="tx-center">4</td> <!-- cantidad de libras a facturar -->
                          <td class="tx-right">$2.50</td> <!-- Tarifa de la Libra -->
                          <td class="tx-right">$10.00</td> <!-- Total sumado de la Libra -->
                        </tr>
                        <tr>
                          <td>Comision Tarjeta</td>
                          <td class="tx-12">Comision por uso de tarjeta de credito</td>
                          <td class="tx-center">1</td>
                          <td class="tx-right">$5.00</td> <!-- Siempre sera Esta Tarifa -->
                          <td class="tx-right">$5.00</td>
                        </tr>
                        --}}

                        <tr>
                          <td colspan="2" rowspan="4" class="valign-middle">
                            <div class="invoice-notes">
                              <label class="section-label-sm tx-gray-500">{{ __('Notes') }}.</label>
                              <p>{{ $invoice->notes }}</p>
                            </div><!-- invoice-notes -->
                            <div class="invoice-notes">
                              <label class="section-label-sm tx-gray-500">{{ __('Tracking numbers') }}</label>
                              <p>
                                  @if ($invoice->warehouse)
                                    {{ $invoice->warehouse->trackings }}
                                  @endif
                              </p>
                            </div><!-- invoice-notes -->
                          </td>
                          <td class="tx-right">{{ __('Total') }}</td>
                          <td colspan="2" class="tx-right">${{ number_format( $invoice->total, 2 ) }}</td>
                        </tr>
                        <tr>
                          <td class="tx-right">{{ __('Credit') }}</td>
                          <td colspan="2" class="tx-right">${{ number_format( $invoice->payments->sum('amount_paid'), 2) }}</td> <!-- SOLO APLICAN LOS ABONOS EN FACTURAS DE COMPRA POR INTERNET-->
                        </tr>

                        <tr>
                          <td class="tx-right tx-uppercase tx-bold tx-inverse">{{ __('Total') }} {{ __('Pendiente') }}</td> <!-- MONTO RESTANTE EN LA FACTURA, SI ES PAGADO COMPLETO ESTE CAMPO DEBE SER IGUAL A CERO 0-->
                          <td colspan="2" class="tx-right"><h4 class="tx-primary tx-bold tx-lato">${{ number_format( $pending = $invoice->total - $invoice->payments->sum('amount_paid'), 2) }}</h4></td>
                        </tr>
                      </tbody>
                    </table>
                  </div><!-- table-responsive -->

                  <hr class="mg-b-50">
                  <div class="row">

                    <div class="col col-sm-3">
                        <button type="button" id="pay" class="btn btn-success btn-block"{{ !$pending ? ' disabled' : null }}>{{ strtoupper( __('New payment') ) }}</button>
                    </div>
                    <!-- se abre el modal con el formulario de registro de pagos-->

                    <div class="col col-sm-3">
                        <button id="send-to-client" class="btn btn-warning btn-block">ENVIAR A CLIENTE</button>
                    </div>
                    <!-- se le reenvia al cliente la factura-->

                    <div class="col col-sm-3">
                        <button id="penalize-client" class="btn btn-danger btn-block">MULTAR CLIENTE</button>
                    </div> 
                    <!-- SE AGREGA UNA LINEA A LA FACTURA CON TITULO DE MULTA POR DEJAR PAQUETS MAS DE 10 DIAS EN BODEGA ( ESTO SOLO APLICA A FACTURAS DE WAREHOUSE)-->

                    <div class="col col-sm-3">
                        <a href="" class="btn btn-primary btn-block">EDITAR / ELIMINAR </a>
                    </div> 
                    <!-- POSIBILIDAD DE EDITR DETALLES DE FACTURAS MAS NO CLIENTE MODIFICAR CLIENTE, ESTO ES CUANDO EL FACTOR HUMANO ENTRA, POSIBILIDAD DE ELIMINARLA, PERO DEBE MOSTRARSE EN LA LISTA DE FACTURA COMO ELIMIDADO Y DEBE AGREGARSE NOTAS DE POR QUE
                    SOLO USUARIO QUE MANEJA CAJA y/o ADMINISTRADORES PUEDEN MODIFICAR O ELIMINAR FACTURA, IGUAL EL LOG DEBE GUARDAR TODO CAMBIO Y DETALLES EFECTUADOS EN LAS FACTURAS-->

                  </div>


                </div><!-- card-body -->
              </div>

              <div class="section-wrapper mg-t-15">

            <div class="mg-b-15">
                      <label class="section-title">Registro de Actividad</label>
                    </div>
                    <div class="col-lg-12">
                        <p>Creado por <b>usuario</b> | <b>00/00/0000</b> | 00:00PM  </p>
                        <p>Modificado por <b>usuario</b> | <b>00/00/0000</b> | 00:00PM  </p>
                        <p>Pago Recibido por <b>usuario</b> | <b>00/00/0000</b> | 00:00PM  </p>
                        <p>Factura Enviada por <b>usuario</b> | <b>00/00/0000</b> | 00:00PM  </p>
                        <p>Registro Enviado por <b>usuario</b> | <b>00/00/0000</b> | 00:00PM  </p>
                      </div>
                  </div>


    </div>
    <!-- container -->
  </div>
  <!-- slim-mainpanel -->
@endsection