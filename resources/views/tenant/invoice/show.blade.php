@extends('layouts.tenant')


@section('title')
    {{ uniqid('invoice_', true) }}
@stop


@section('content')

<!-- slim-mainpanel -->
<div class="slim-mainpanel">
    <div class="container">
      <div class="slim-pageheader hidden-print d-print-none">
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
                        <h6 class="tx-gray-800">{{ $invoice->client->full_name }} / {{ $invoice->client->branch->code }}
                            {{ $invoice->client->manual_id_dsp }}
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
                        @if (!$invoice->warehouse_id)
                            <tr>
                                <th class="wd-20p">{{ __('Type') }}</th> <!--Compra por internet / Encomienda / Comision Tarjeta-->
                                <th class="wd-40p">{{ __('Description') }}</th><!-- Una Compra en (sitio web) / Un Flete Aereo. Maritimo en caso de warehouse-->
                                <th class="tx-center">{{ __('Qty') }}</th> <!--cantidad de libras a facturar-->
                                <th class="tx-right">{{ __('Price') }}</th> <!--en este caso deberia aplicar aca 2,50 o el precio estipulado de las libras-->
                                <th class="tx-right">{{ __('Amount') }}</th>
                            </tr>
                        @else
                            <tr>
                                <th class="wd-20p">{{ __('Type') }}</th>
                                <th class="wd-40p">{{ __('Description') }}</th>
                                <th class="tx-center">{{ __('Qty') }}</th>
                                <th class="tx-right">{{ __('Price') }}</th>
                                <th class="tx-right">{{ __('Amount') }}</th>
                            </tr>
                        @endif
                      </thead>
                      <tbody>

                        @if (!$invoice->warehouse_id)
                            
                            @foreach ($invoice->details as $detail)
                                <tr>
                                    <td>{{ $detail->productType ? $detail->productType->name : 'N/A' }}</td>
                                    <td class="tx-12">{{ $detail->description }}</td>
                                    <td class="tx-center">{{ $detail->qty }}</td>
                                    <td class="tx-right">${{ number_format( $detail->total, 2 ) }}</td>
                                    <td class="tx-right">${{ number_format( $detail->qty * $detail->total, 2 ) }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($invoice->details as $detail)
                                <tr>
                                    <td>{{ ['A' => __('Air'), 'M' => __('Maritime'), ][$invoice->warehouse->type] }}</td>
                                    <td>{{ [1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', ][$detail->type] }}</td>
                                    <td class="tx-center">
                                        @if ($invoice->i_using == 'V')
                                           {{ $detail->vol_weight }}
                                        @elseif ($invoice->i_using == 'R')
                                           {{ $detail->real_weight }}
                                        @else
                                        @endif
                                    </td>
                                    <td class="tx-right">
                                        @if ($invoice->i_using == 'V')
                                           ${{ number_format( $detail->vol_price, 2 ) }}
                                        @elseif ($invoice->i_using == 'R')
                                           ${{ number_format( $detail->real_price, 2 ) }}
                                        @else
                                        @endif
                                    </td>
                                    <td class="tx-right">${{ number_format( $detail->total, 2 ) }}</td>
                                </tr>
                            @endforeach
                        @endif

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
                              <p>
                                {{ $invoice->notes }}
                                @if ($invoice->fine_total)
                                <br>
                                <b><em>
                                    {{ __(':qty fine is included', ['qty' => '$ ' . number_format($invoice->fine_total, 2) ]) }}
                                    <br>
                                    {{ $invoice->fine_ref }}
                                </em></b>
                                @endif
                              </p>
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
                          <td colspan="2" class="tx-right">$<span id="total-dsp">{{ number_format( $invoice->total, 2 ) }}</span></td>
                        </tr>
                        <tr>
                          <td class="tx-right">{{ __('Credit') }}</td>
                          <td colspan="2" class="tx-right">$<span id="amount_paid-dsp">{{ number_format( $invoice->payments->sum('amount_paid'), 2) }}</span></td> <!-- SOLO APLICAN LOS ABONOS EN FACTURAS DE COMPRA POR INTERNET-->
                        </tr>

                        <tr>
                          <td class="tx-right tx-uppercase tx-bold tx-inverse">{{ __('Total') }} {{ __('Pendiente') }}</td> <!-- MONTO RESTANTE EN LA FACTURA, SI ES PAGADO COMPLETO ESTE CAMPO DEBE SER IGUAL A CERO 0-->
                          <td colspan="2" class="tx-right"><h4 class="tx-primary tx-bold tx-lato">$<span id="pending-dsp">{{ number_format( $pending = $invoice->total - $invoice->payments->sum('amount_paid'), 2) }}</span></h4></td>
                        </tr>
                      </tbody>
                    </table>
                  </div><!-- table-responsive -->

                  <hr class="mg-b-50">
                  <div class="row hidden-print d-print-none" id="btns-container">

                    <div class="col-md-2 mg-t-10">
                        <button type="button" id="pay" class="btn btn-outline-success btn-block terminate"{{ !$pending || $invoice->status == 'I' ? ' disabled' : null }} data-toggle="modal" data-target="#modal-payment">
                          {{ strtoupper( __('New payment') ) }}
                        </button>
                    </div>
                    <!-- se abre el modal con el formulario de registro de pagos-->

                    <div class="col-md-3 mg-t-10">
                        <button id="send-to-client" class="btn btn-outline-warning btn-block terminate" data-loading-text="<i class='fa fa-spinner fa-spin '></i>"
                        {{ $invoice->status == 'I' || $invoice->client->email === $tenant->email_allowed_dup ? ' disabled' : null }}
                        >
                         {{ strtoupper( __('Send to :who', ['who' => __('Client')]) ) }}
                        </button>
                    </div>
                    <!-- se le reenvia al cliente la factura-->

                    <div class="col-md-3 mg-t-10">
                        <button id="penalize-client" class="btn btn-outline-purple btn-block terminate"{{ !$pending || $invoice->status == 'I' ? ' disabled' : null }} data-toggle="modal" data-target="#modal-penalize">
                          {{ strtoupper( __('Fine :who', ['who' => __('Client')]) ) }}
                        </button>
                    </div> 
                    <!-- SE AGREGA UNA LINEA A LA FACTURA CON TITULO DE MULTA POR DEJAR PAQUETS MAS DE 10 DIAS EN BODEGA ( ESTO SOLO APLICA A FACTURAS DE WAREHOUSE)-->

                    <div class="col-md-2 mg-t-10">
                        @can('edit-invoice')
                        <a href="{{ route('tenant.invoice.edit', [$tenant->domain, $invoice->id, 'branch_id' => $invoice->branch->id, 'client_id' => request('client_id'), ]) }}" class="btn btn-outline-primary btn-block">
                            {{ strtoupper( __('Edit') ) }}
                        </a>
                        @endcan
                    </div>

                    <div class="col-md-2 mg-t-10">
                        @can('delete-invoice')
                            <button id="btn-delete" class="btn btn-outline-danger btn-block terminate"{{ $invoice->is_paid || $invoice->status == 'I' ? ' disabled' : null }}
                            data-loading-text="<i class='fa fa-spinner fa-spin '></i> ..."
                            >
                            {{ strtoupper( __('Delete') ) }}
                            </button>
                        @endcan
                    </div> 
                    
                    <!-- POSIBILIDAD DE EDITR DETALLES DE FACTURAS MAS NO CLIENTE MODIFICAR CLIENTE, ESTO ES CUANDO EL FACTOR HUMANO ENTRA, POSIBILIDAD DE ELIMINARLA, PERO DEBE MOSTRARSE EN LA LISTA DE FACTURA COMO ELIMIDADO Y DEBE AGREGARSE NOTAS DE POR QUE
                    SOLO USUARIO QUE MANEJA CAJA y/o ADMINISTRADORES PUEDEN MODIFICAR O ELIMINAR FACTURA, IGUAL EL LOG DEBE GUARDAR TODO CAMBIO Y DETALLES EFECTUADOS EN LAS FACTURAS-->

                  </div>

                  <div class="row mg-t-10 hidden-print d-print-none">
                      <div class="col">
                      
                      @if (config('app.invoice_print_version') == 2)
                          <a href="#" id="btn-print-invoice" class="btn btn-outline-dark btn-block" role="button" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                                <i class="fa fa-print"></i> {{ strtoupper( __('Print :what', ['what' => __('Invoice') ]) ) }}
                            </a>
                      @else
                          <a target="_blank" href="{{ route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id, ]) }}" class="btn btn-outline-dark btn-block" role="button" title="{{ __('Print :what', ['what' => __('Invoice') ]) }}">
                                <i class="fa fa-print"></i> {{ strtoupper( __('Print :what', ['what' => __('Invoice') ]) ) }}
                            </a>
                      @endif
                      </div>
                  </div>


                </div><!-- card-body -->
              </div>

              <div class="section-wrapper mg-t-15">
                <h3>{{ __('Terms and Conditions') }}</h3>
                @if ($invoice->warehouse_id)
                    @if ($condition = $tenant->conditionsWarehouse->first())
                        <p>{{ $condition->content }}</p>
                    @endif
                @else
                    @if ($condition = $tenant->conditionsInvoice->first())
                        <p>{{ $condition->content }}</p>
                    @endif  
                @endif
                
              </div>

              <div class="section-wrapper mg-t-15 hidden-print d-print-none">

                    <div class="mg-b-15">
                      <label class="section-title">{{ __('Activity Log') }}</label>
                    </div>
                    <div class="col-lg-12">

                        @if ($invoice->creator)
                            <p>{{ __('Created by') }} <b>{{ $invoice->creator->full_name }}</b> | <b>{{ $invoice->created_at->format('d/m/Y') }}</b> | {{ $invoice->created_at->format('g:i A') }} </p>
                        @endif

                        @if ($invoice->editor)
                            <p>{{ __('Edited by') }} <b>{{ $invoice->editor->full_name }}</b> | <b>{{ $invoice->updated_at->format('d/m/Y') }}</b> | {{ $invoice->updated_at->format('g:i A') }} </p>
                        @endif

                        <?php $lPayment = $invoice->payments->last(); ?>
                        @if ($lPayment && $lPayment->creator)
                            <p>{{ __('Last payment by') }} <b>{{ $lPayment->creator->full_name }}</b> | <b>{{ $lPayment->created_at->format('d/m/Y') }}</b> | {{ $lPayment->created_at->format('g:i A') }} </p>
                        @endif

                        @if ($invoice && $invoice->is_paid)
                            <p>{{ __('Delivered by') }} <b>{{ $lPayment->creator->full_name }}</b> | <b>{{ $lPayment->created_at->format('d/m/Y') }}</b> | {{ $lPayment->created_at->format('g:i A') }} </p>
                        @endif
                        
                        @if ($invoice->status == 'I')
                            <p>{{ __('Deleted by') }} <b>{{ $invoice->editor->full_name }}</b> | <b>{{ $invoice->updated_at->format('d/m/Y') }}</b> | {{ $invoice->updated_at->format('g:i A') }} </p>
                        @endif

                      </div>
                  </div>


    </div>
    <!-- container -->
  </div>
  <!-- slim-mainpanel -->

@include('common._modal-payment', [
  'payments' => $invoice->payments,
])

@include('common._modal-penalize', [])

@endsection

@section('xtra_scripts')
  <script>

    function printing() {
        window.print();
    }
  
    $(function() {

        // printin ginvoice
        @if (request('__printing'))
            printing();
        @endif

        $("#btn-print-invoice").click(function(e){
            e.preventDefault();
            window.print();
        });

        // payment
        var $baseModalPayment = $('#modal-payment');
        var $launcherPayment = null;

       $("#pay").click(function(e) {
            $launcherPayment = $(this);
            $baseModalPayment.on('shown.bs.modal', function () {
               $launcherPayment.prop('disabled', true);
            });
        });


        $('#btn-cancel-payment').click(function() {
            $("#p_amount_paid, #p_payment_method, #p_payment_ref").val("");
            $launcherPayment.prop('disabled', false);
        });

        $("#form-payment").submit(function(e) {
            
            var $btnSubmit = $('#btn-submit-payment');
            var url = "{{ route('tenant.payment.store', $tenant->domain) }}";
            var loadingText = $btnSubmit.data('loading-text');

            if ($btnSubmit.html() !== loadingText) {
                $btnSubmit.data('original-text', $btnSubmit.html());
                $btnSubmit.prop('disabled', true).html(loadingText);
            }

            var request = $.ajax({
                method: 'post',
                url: url,
                data: $.extend({
                    _token	: $("meta[name='csrf-token']").attr('content'),
                    '_method': 'POST',
                    'invoice_id': "{{ $invoice->id }}",
                    'amount_paid': $("#p_amount_paid").val(),
                    'payment_method': $("#p_payment_method").val(),
                    'payment_ref': $("#p_payment_ref").val(),
                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal("", data.msg, "success");
                    $("#p_amount_paid, #p_payment_method, #p_payment_ref").val("");

                    $("#pending-dsp").html(data.pending);
                    $("#amount_paid-dsp").html(data.totalPaid);
                    $("#p_amount_paid").attr('max', data.pending);
                    $baseModalPayment.modal('hide');

                    var p = parseFloat(data.pending || 0);

                    if (!p) $launcherPayment.prop('disabled', true);
                    else $launcherPayment.prop('disabled', false);

                } else {
                    swal("", data.msg, "error");
                }

                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                
                var error = "{{ __('Error') }}";

                if (jqXHR.responseJSON.msg) {
                    error = jqXHR.responseJSON.msg;
                }
                
                swal("", error, "error");
                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));

                $launcherPayment.prop('disabled', false);
            });

            e.preventDefault();
        }); // payment

        // resend invoice
        $("#send-to-client").click(function() {
            var $self = $(this);
            var loadingText = $self.data('loading-text');

            if ($(this).html() !== loadingText) {
                $self.data('original-text', $(this).html());
                $self.prop('disabled', true).html(loadingText);
            }

            var request = $.ajax({
                method: 'post',
                url: "{{ route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id, ]) }}",
                data: $.extend({
                    _token	: $("meta[name='csrf-token']").attr('content'),
                    '_method': 'POST',
                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal(data.msg, "", "success");
                } else {
                    swal(data.msg, "", "error");
                }

                $self.prop('disabled', false).html($self.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                swal(textStatus, "", "error");
                $self.prop('disabled', false).html($self.data('original-text'));
            });

        });
        // resend invoice

        // fine
        var $baseModalPenalize = $('#modal-penalize');
        var $launcherPenalize = null;

        $("#penalize-client").click(function(e) {
            $launcherPenalize = $(this);
            $baseModalPenalize.on('shown.bs.modal', function () {
               $launcherPenalize.prop('disabled', true);
            });
        });


        $('#btn-cancel-penalize').click(function() {
            $("#fine_total, #fine_ref").val("");
            $launcherPenalize.prop('disabled', false);
        });

        $("#form-penalize").submit(function(e) {
            
            var $btnSubmit = $('#btn-submit-penalize');
            var url = "{{ route('tenant.invoice.penalize', $tenant->domain) }}";
            var loadingText = $btnSubmit.data('loading-text');

            if ($btnSubmit.html() !== loadingText) {
                $btnSubmit.data('original-text', $btnSubmit.html());
                $btnSubmit.prop('disabled', true).html(loadingText);
            }

            var request = $.ajax({
                method: 'post',
                url: url,
                data: $.extend({
                    _token	: $("meta[name='csrf-token']").attr('content'),
                    '_method': 'POST',
                    'invoice_id': "{{ $invoice->id }}",
                    'fine_total': $("#fine_total").val(),
                    'fine_ref': $("#fine_ref").val(),
                }, {})
            });

            request.done(function(data){
                if (data.error == false) {
                    swal("", data.msg, "success");
                    $("#fine_total, #fine_ref").val("");

                    $("#total-dsp").html(data.total);
                    $("#pending-dsp").html(data.pending);
                    $("#amount_paid-dsp").html(data.totalPaid);
                    $baseModalPenalize.modal('hide');

                    var p = parseFloat(data.pending || 0);

                    if (!p) $launcherPenalize.prop('disabled', true);
                    else $launcherPenalize.prop('disabled', false);

                } else {
                    swal("", data.msg, "error");
                }

                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));
            })
            .fail(function( jqXHR, textStatus ) {
                
                var error = "{{ __('Error') }}";

                if (jqXHR.responseJSON.msg) {
                    error = jqXHR.responseJSON.msg;
                }
                
                swal("", error, "error");
                $btnSubmit.prop('disabled', false).html($btnSubmit.data('original-text'));

                $launcherPenalize.prop('disabled', false);
            });

            e.preventDefault();
        }); // fine

        // delete
        $("#btn-delete").click(function(e) {
            var self = $(this);
            swal({
                title: '{{__("Are you sure") }}?',                    
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: '{{ __("No") }}',
                confirmButtonText: '{{ __("Yes") }}'
            })
            .then((result) => {
                if (result.value) {
                    inactivate(self);
                }
            });
         });
        // delete

    }); // jquery


    function inactivate($btnSubmit) {
        $(".terminate").prop('disabled', true);
        var loadingText = $btnSubmit.data('loading-text');

            if ($btnSubmit.html() !== loadingText) {
                $btnSubmit.data('original-text', $btnSubmit.html());
                $btnSubmit.html(loadingText);
            }

        var request = $.ajax({
            method: 'post',
            url: "{{ route('tenant.invoice.inactive', $tenant->domain) }}",
            data: $.extend({
                _token	: $("meta[name='csrf-token']").attr('content'),
                '_method': 'POST',
                'invoice_id': "{{ $invoice->id }}",
            }, {})
        });

        request.done(function(data){
            if (data.error == false) {
                swal("", data.msg, "success");
            } else {
                swal("", data.msg, "error");
            }
            $btnSubmit.html($btnSubmit.data('original-text'));
        })
        .fail(function( jqXHR, textStatus ) {
            
            var error = "{{ __('Error') }}";

            if (jqXHR.responseJSON.msg) {
                error = jqXHR.responseJSON.msg;
            }
            
            swal("", error, "error");
            $(".terminate").prop('disabled', false);
            $btnSubmit.html($btnSubmit.data('original-text'));
        });
    }
  
  </script>
@endsection


@section('xtra_styles')
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 1.6cm; }
        }
    </style>
@stop
