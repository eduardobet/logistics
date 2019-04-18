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

            <div class="col-lg-2">
                 <select name="branch_id" id="branch_id" class="form-control select2 select2ize" style="width: 100%" data-apiurl="{{ route('tenant.api.clients', [':parentId:']) }}" data-child="#client_id">
                    <option value="">{{ __('Branch') }}</option>
                    @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == request('branch_id', $branch->id) ? " selected" : null }} data-bcode="{{ $aBranch->initial }}" data-bname="{{ $aBranch->name }}">
                            {{ $aBranch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            #{{ __('Invoice') }}
                        </div>
                    </div>
                     <input type="text" class="form-control" value="{{ request('manual_id') }}" id="manual_id">
                </div>
            </div>

            <div class="col-lg-2">
                
                <div class="input-group">
                    {!! Form::select('type', ['' => '---'. __('Payment method') . '---', 1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], request('type'), ['class' => 'form-control', 'id' => 'type' ]) !!}
                    <div class="input-group-append">
                        <button class="btn" type="button" id="filter">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-2">
                <a class="btn btn-outline-primary" title="PDF" id="export-pdf" href="#!" data-href="{{ route('tenant.income.list', ['domain' => $tenant->domain, 'branch_id' => request('branch_id'), 'from' => request('from'), 'to' => request('to'), 'type' => request('type'), 'bcode' => request('bcode'), 'bname' => request('bname'), '_print_it' => 1, ]) }}">
                    <i class="fa fa-file-pdf-o"></i>
                </a>

                <a class="btn btn-outline-primary" title="Excel" id="export-xls" href="#!" data-href="{{ route('tenant.income.list', ['domain' => $tenant->domain, 'branch_id' => request('branch_id'), 'from' => request('from'), 'to' => request('to'), 'type' => request('type'), 'bcode' => request('bcode'), 'bname' => request('bname'), '_excel_it' => 1, ]) }}">
                    <i class="fa fa-file-excel-o"></i>
                </a>

            </div>

        </div>
        <!--/row-->

        <div class="table-responsive-sm">
            @include('tenant.income._index', ['printing'])
        </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')

    <script>
    $(function() {
        $('.fc-datepicker').datepicker({
          showOtherMonths: true,
          selectOtherMonths: true,
          language: '{{ config("app.locale") }}',
          format: 'yyyy-mm-dd',
          todayBtn: 'linked'
        });

        $("#branch_id").select2({width: 'resolve', 'placeholder': "{{ __('Branch') }}", allowClear: false});
        $("#branch_id").change();

        $("#filter").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var bcode = $("#branch_id").find(":selected").attr('data-bcode') || '{{ $branch->branch_code }}';
            var bname = $("#branch_id").find(":selected").attr('data-bname') || '{{ $branch->name }}';
            var client = $("#client_id").val()  || '';
            var type = $("#type").val();
            var manualId = $("#manual_id").val();
            var invoice = $("#invoice_id").val() || "{{ request('invoice_id', '') }}";
            window.location = `{{ route('tenant.income.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}&invoice_id=${invoice}&bcode=${bcode}&bname=${bname}&manual_id=${manualId}`;
        });

        $("#export-xls, #export-pdf").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var bcode = $("#branch_id").find(":selected").attr('data-bcode') || '{{ $branch->branch_code }}';
            var client = $("#client_id").val() || '';
            var type = $("#type").val();
            var manualId = $("#manual_id").val();
            var invoice = $("#invoice_id").val() || "{{ request('invoice_id', '') }}";
            var pdf = this.id === 'export-pdf' ? '&pdf=1' : '';
            var baseUrl = this.id === 'export-pdf' ? "{{ route('tenant.income.export-pdf', $tenant->domain) }}" : "{{ route('tenant.income.export-excel', $tenant->domain) }}";

            if(from && to) window.open(`${baseUrl}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}&invoice_id=${invoice}&bcode=${bcode}&manual_id=${manualId}${pdf}`, '_blank');
        });
    });
</script>
@stop