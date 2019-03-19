@extends('layouts.tenant', ['noSearch' => true, ])

@section('title')
    {{ uniqid('receipt_', true) }}
@stop


@section('content')

  <div class="slim-mainpanel">
  <div class="container">
     @if (!request('__print_it') && !request('__send_it'))
      <div class="slim-pageheader hidden-print d-print-none">
        <ol class="breadcrumb slim-breadcrumb">
          <li class="breadcrumb-item"><a href="/">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]) }}">{{ __('Warehouse', [], $lang) }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('Warehouse receipt', [], $lang) }}</li>
        </ol>
        <h6 class="slim-pagetitle">{{ __('Warehouse receipt', [], $lang) }}</h6>
      </div><!-- slim-pageheader -->
    @endif

    <div class="card card-invoice">
      <div class="card-body">
        <div class="invoice-header">
          <h4 class="invoice-title">{{ __('Warehouse receipt', [], $lang) }}</h4>
          <div class="billed-from">
            <h6>{{ $branchTo->name }}</h6>
            <p>{{ $branchTo->address }}<br>
            Tel {{ $branchTo->telephones }}<br>
            Email: {{ $branchTo->emails }}</p>
          </div><!-- billed-from -->
        </div><!-- invoice-header -->

        <div class="row mg-t-20">
          <div class="col-5">
                      <label class="section-label-sm tx-gray-500">{{ __('For', [], $lang) }}</label>
            <div class="billed-to">
              <h6 class="tx-gray-800">{{ $client->full_name }}</h6>
              <p>{{ $client->address }} <br>
              Tel: {{ $client->telephones }}<br>
              Email: {{ $client->email }}</p>
            </div>
          </div><!-- col -->
          <div class="col-7">
            <label class="section-label-sm tx-gray-500">{{ __('Warehouse details', [], $lang) }}</label>
            <p class="invoice-info-row">
              <span>#</span>
              <span class="tx-bold tx-16">WH-{{ $warehouse->manual_id }}</span>
              <span>{{ __('Pieces', [], $lang) }}:</span>
              <span class="tx-bold tx-16">{{ $warehouse->tot_packages }}</span>
            </p>

            <p class="invoice-info-row">
              <span class="tx-bold">{{ __('Type', [], $lang) }}:</span>
              <span class="tx-bold tx-16">{{ ['A' => __('Air', [], $lang), 'M' => __('Maritime', [], $lang) ][$warehouse->type]}}</span>
            </p>
            
            <p class="invoice-info-row">
              <span>{{ __('Gross weight', [], $lang) }}:</span>
              <span class="tx-bold tx-16">{{ number_format($warehouse->tot_weight, 2) }}</span>
              <span>{{ __('Cubic feet', [], $lang) }}:</span>
            <span class="tx-bold tx-16">{{ number_format($invoice->cubic_feet, 2) }}</span>
            </p>

            <p class="invoice-info-row">
              <span>{{ __('Mailer', [], $lang) }}:</span>
              <span class="tx-bold tx-16">{{ optional($mailer)->name }}</span>
            </p>

            <p class="invoice-info-row">
              <span>{{ __('Receipt date', [], $lang) }}:</span>
              <span>{{ $warehouse->created_at->format('M d, Y') }}</span>
            </p>
          </div><!-- col -->

        </div><!-- row -->

        <div class="table-responsive mg-t-40">
          <table class="table table-invoice table-bordered" >
            <thead>
              <tr>
                <th class="wd-10p">{{ __('Pieces', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Type', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Length', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Width', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Height', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Weight', [], $lang) }}</th>
                <th class="wd-10p">{{ __('Type', [], $lang) }}/p</th>
                <th class="wd-10p">Vol.</th>

              </tr>
            </thead>
            <tbody >

                @foreach ($invoice->details as $detail)
                    
                <tr>
                    <td>{{ $detail->qty }}</td>
                    <td>
                        {{ [1=>'Sobre',2=>'Bulto', 3=>'Paquete',4=>'Caja/Peq.', 5=>'Caja/Med.', 6=>'Caja/Grande', 7=>'Servicio aéreo' ][$detail->type] }}
                    </td>
                    <td> {{ $detail->length }} </td>
                    <td> {{ $detail->width }} </td>
                    <td> {{ $detail->height }} </td>
                    <td> {{ $detail->real_weight }} </td>
                    <td> - </td>
                    <td> {{ $detail->vol_weight }} </td>
                </tr>

                @endforeach
             
              <tr>
                <td colspan="6" rowspan="7" class="valign-middle">
                  <div class="invoice-notes">
                    <label class="section-label-sm tx-gray-500">{{ __('Notes', [], $lang) }}</label>
                    <p>{{ $invoice->notes }}</p>
                  </div><!-- invoice-notes -->
                </td>
              </tr>
              <tr>
                <td class="tx-right">LBS</td>
                <td   class="tx-right">
                  <?php
                    $lbs = 0;
                    if ($invoice->i_using == 'R') $lbs = $invoice->real_weight;
                    else if ($invoice->i_using == 'V') $lbs = $invoice->volumetric_weight;
                  ?>
                  {{ number_format($lbs, 2) }}
                </td>
              </tr>
              <tr>
                <td class="tx-right">KGS</td>
                <td  class="tx-right">
                    @if ($invoice->i_using == 'R' || $invoice->i_using == 'V')
                        {{ number_format($lbs / 2.2046, 2)}}
                    @endif
                </td>
              </tr>
              <tr>
                <td class="tx-right">{{ __('Cubic feet', [], $lang) }}</td>
                <td  class="tx-right">{{ number_format($invoice->cubic_feet, 2) }}</td>
              </tr>
              <tr>
                <td class="tx-right">{{ __('Cubic meters', [], $lang) }}</td>
                <td  class="tx-right">{{ number_format($invoice->cubic_feet / 35.315, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div><!-- table-responsive -->

        <hr class="mg-b-60">

        @if (!request('__print_it') && !request('__send_it'))
        <div class="hidden-print d-print-none">
            <a href="{{ route('tenant.warehouse.receipt', [$tenant->domain, $warehouse->id]) }}?__print_it=1" class="btn btn-warning"><i class="fa fa-print"></i>
              {{ strtoupper( __('Print :what', ['what' => __('Receipt', [], $lang) ], [], $lang) ) }}
            </a>

            <button type="button" id="send-to-client" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin '></i>">
              <i class="fa fa-envelope-o"></i>
              {{ strtoupper( __('Send to :who', ['who' => __('Client', [], $lang)], [], $lang) ) }}
            </button>
            
        </div>
        @endif

      </div><!-- card-body -->
    </div><!-- card -->

  </div><!-- container -->
</div><!-- slim-mainpanel -->


@endsection


@section('xtra_styles')
    <style>
        @media print {
            @page { margin: 0;  size: auto; }
            body { margin: 1.6cm; }
        }

        .table td {padding: 0.05rem !important;}
    </style>
@endsection

@section('xtra_scripts')
  <script>
    $(function() {

      $("#send-to-client").click(function() {
            var $self = $(this);
            var loadingText = $self.data('loading-text');

            if ($(this).html() !== loadingText) {
                $self.data('original-text', $(this).html());
                $self.prop('disabled', true).html(loadingText);
            }

            var request = $.ajax({
                method: 'GET',
                url: "{{ route('tenant.warehouse.receipt', [$tenant->domain, $warehouse->id, ]) }}?__send_it=1",
                data: $.extend({
                    _token	: $("meta[name='csrf-token']").attr('content'),
                    '_method': 'GET',
                    'id': {{ $warehouse->id }}
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



    });
  </script>
@endsection