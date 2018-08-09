@extends('layouts.tenant')

@section('title')
  {{ __('Warehouses') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
           {{ Breadcrumbs::render() }}

           @can('create-warehouse')
             <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.warehouse.create', $tenant->domain) }}">
                <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
             </a>
            @endcan
          
        </div><!-- slim-pageheader -->

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

            <div class="col-lg-5">
                 <select name="branch_id" id="branch_id" class="form-control select2" style="width: 100%">
                    <option value="">{{ __('Branch') }}</option>
                    @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == $branch->id ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3">
                
                <div class="input-group">
                    {!! Form::select('type', ['' => __('Type'), 'A' => __('Air'), 'M' => __('Maritime'), ], null, ['class' => 'form-control', 'id' => 'type', ]) !!}
                    <div class="input-group-append">
                        <button class="btn" type="button" id="filter">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="table-responsive-sm">
            @include('tenant.warehouse._index', ['warehouses' => $warehouses])
            
            @if ($searching == 'N')
              <div id="result-paginated" class="mg-t-25">
                  {{ $warehouses->links() }}
              </div> 
           @endif
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

        $("#branch_id").select2({width: 'resolve', 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#filter").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var type = $("#type").val();
            window.location = `{{ route('tenant.warehouse.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&type=${type}`;
        });

        $("#export-xls, #export-pdf").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var type = $("#type").val();
            var pdf = this.id === 'export-pdf' ? '&pdf=1' : '';
            
            if(from && to) window.open(`{{ route('tenant.warehouse.export', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&type=${type}${pdf}`, '_blank');
        });
    });
</script>
@stop
