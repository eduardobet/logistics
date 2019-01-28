@extends('layouts.tenant')

@section('title')
  {{ __('Incomes') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}
           
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>

        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="row mg-b-10">

            <div class="col-lg-2">

                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('From') }}
                        </div>
                    </div>
                    <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('from', date('Y-m-d')) }}" id="from">
                </div>

            </div>

            <div class="col-lg-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('To') }}
                        </div>
                    </div>
                     <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('to', date('Y-m-d')) }}" id="to">
                </div>
            </div>

            <div class="col-lg-3">
                 <select name="branch_id" id="branch_id" class="form-control select2 select2ize" style="width: 100%" data-apiurl="{{ route('tenant.api.clients', [':parentId:']) }}" data-child="#client_id">
                    <option value="">{{ __('Branch') }}</option>
                    @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == request('branch_id', $branch->id) ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3">
                 {!! Form::select('client_id', ['' => '----'], null, ['class' => 'form-control select2', 'id' => 'client_id', 'width' => '100% !important', ]) !!}
            </div>

            <div class="col-lg-2">
                
                <div class="input-group">
                    {!! Form::select('type', ['' => __('Payment method'), 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], request('type'), ['class' => 'form-control', 'id' => 'type' ]) !!}
                    <div class="input-group-append">
                        <button class="btn" type="button" id="filter">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <!--/row-->

        <div class="card card-invoice">
          <div class="card-body">
            <div class="invoice-header">
              <h1 class="invoice-title">REPORTE, 00/00/2019</h1>

            </div><!-- invoice-header -->

            <div class="row mg-t-20">
              <div class="col-md">
                <label class="section-label-sm tx-gray-500">Detalles Sucursal y Fecha</label>
                <div class="billed-to">
                  <h6 class="tx-gray-800">PREMIUM RUSH LOS ANDES</h6>
                  <p><b>FECHA:</b> <br>Desde: 00/00/2019<br> Hasta: 00/00/2019<br>
                </div>
              </div><!-- col -->
              <div class="col-md">
                <label class="section-label-sm tx-gray-500">Informacion Detallada</label>
                <p class="invoice-info-row">
                  <span>Total Facturado</span>
                  <span>$0000,00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Cobrado</span>
                  <span>$0000.00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Efectivo:</span>
                  <span>$0000,00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Total Deposito/Transferencia:</span>
                  <span>$0000,00</span>
                </p>

              </div><div class="col-md">
                <label class="section-label-sm tx-gray-500">Informacion Detallada</label>
                <p class="invoice-info-row">
                  <span>Total Cheques:</span>
                  <span>$0000,00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Comision Tarjeta:</span>
                  <span>$0000,00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Multa Almacenaje:</span>
                  <span>$0000.00</span>
                </p>
                <p class="invoice-info-row">
                  <span>Libras Ingresadas:</span>
                  <span>000LBS</span>
                </p>

              </div><!-- col -->
            </div><!-- row -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice" >
                <thead class="thead-colored bg-primary">
                  <tr>
                    <th class="wd-10p">COBRO EFECTIVO</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice" >
                <thead class="thead-colored bg-success">
                  <tr>
                    <th class="wd-10p">COBRO DEPOSITOS / TRANSFERENCIAS</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice" >
                <thead class="thead-colored bg-danger">
                  <tr>
                    <th class="wd-10p">COBRO CHEQUES</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice" >
                <thead class="thead-colored bg-warning">
                  <tr>
                    <th class="wd-10p">COBRO ALMACENAJE</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice">
                <thead class="thead-colored bg-teal">
                  <tr>
                    <th class="wd-10p">COBRO COMISION TARJETA</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->

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
                  <tr>
                    <td>00/00/2019</td>
                    <td>RECA-0000</td>
                    <td>000</td>
                    <td class="tx-right">000LBS</td>
                  </tr>

                </tbody>
              </table>
            </div><!-- table-responsive -->

            <div class="table-responsive mg-t-40 table-bordered">
              <table class="table table-invoice">
                <thead class="thead-colored bg-indigo">
                  <tr>
                    <th class="wd-10p">COBROS MIXTOS</th>
                  </tr>
                </thead>
              </table>
              <table class="table table-invoice" >
                <thead>
                  <tr>
                    <th class="wd-10p">FACTURA</th>
                    <th class="wd-10p">FECHA</th>
                    <th class="wd-40p">TIPO DE FACTURA</th>
                    <th class="wd-10p tx-center">TOTAL</th>
                    <th class="wd-10p tx-right">AOBONO/PAGO</th>
                    <th class="wd-10p tx-right">PENDIENTE</th>
                  </tr>
                </thead>
                <tbody class="mg-b-0">
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                  <tr>
                    <td>XX-000</td>
                    <td>00/00/2019</td>
                    <td>WH-000 / COMPRA / SERVICIO</td>
                    <td class="tx-center">$000.00</td>
                    <td class="tx-right">$000.00</td>
                    <td class="tx-right">$300.00</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->


            <hr class="mg-b-30">

                        <a href="" class="btn btn-success btn-block">IMPRIMIR REPORTE</a>

          </div><!-- card-body -->
        </div><!-- card -->











































      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')

@include('common._select2ize')
<script>
select2ize = function($child, items) {
    var newOptions = '<option value="">{{ __("Client") }}</option>';
        for(var key in items) {
            var obj = items[key];
            var box = obj.branch.code;
            newOptions += `
                <option value='${obj.id}' ${obj.id=="{{request('client_id', 'NA')}}"?" selected":''}>
                   [${box}${obj.manual_id_dsp}] ${obj.full_name}
                </option>`;
        }
        
        $child.select2('destroy').html(newOptions).prop("disabled", false)
        .select2({width: 'resolve', placeholder: '{{ __("Client") }}', language: "{{ config('locale.lang') }}", allowClear: true});
}
</script>



    <script>
    $(function() {
        $('.fc-datepicker').datepicker({
          showOtherMonths: true,
          selectOtherMonths: true,
          language: '{{ config("app.locale") }}',
          format: 'yyyy-mm-dd',
          todayBtn: 'linked'
        });

        $("#branch_id").select2({width: 'resolve', 'placeholder': "{{ __('Branch') }}", allowClear: true});
        $("#branch_id").change();

        $("#filter").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var type = $("#type").val();
            var invoice = $("#invoice_id").val() || "{{ request('invoice_id', '') }}";
            window.location = `{{ route('tenant.payment.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}&invoice_id=${invoice}`;
        });

        $("#export-xls, #export-pdf").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var type = $("#type").val();
            var invoice = $("#invoice_id").val() || "{{ request('invoice_id', '') }}";
            var pdf = this.id === 'export-pdf' ? '&pdf=1' : '';
            
            if(from && to) window.open(`{{ route('tenant.payment.export', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}&invoice_id=${invoice}${pdf}`, '_blank');
        });
    });
</script>
@stop