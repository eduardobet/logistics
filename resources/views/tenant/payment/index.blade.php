@extends('layouts.tenant')

@section('title')
  {{ __('Payments') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}
           
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>

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

            <div class="col-lg-3">
                 <select name="branch_id" id="branch_id" class="form-control select2 select2ize" style="width: 100%" data-apiurl="{{ route('tenant.api.clients', [':parentId:']) }}" data-child="#client_id">
                    <option value="">{{ __('Branch') }}</option>
                    @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == $branch->id ? " selected" : null }}>
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


        <div class="table-responsive-sm">

            @include('tenant.payment._index', ['payments' => $payments])
            
            @if ($searching == 'N')
                <div id="result-paginated" class="mg-t-25">
                    {{ $payments->links() }}
                </div>
            @endif

          </div>

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
            var box = obj.boxes[0];
            newOptions += `
                <option value='${obj.id}' ${obj.id=={{request('client_id')}}?" selected":null}>
                   [${box.branch_code}${obj.id}] ${obj.full_name}
                </option>`;
        }
        
        $child.select2('destroy').html(newOptions).prop("disabled", false)
        .select2({width: 'resolve', placeholder: '{{ __("Client") }}', language: "es"});
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

        $("#branch_id").select2({width: 'resolve', 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#filter").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var type = $("#type").val();
            window.location = `{{ route('tenant.payment.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}`;
        });

        $("#export-xls, #export-pdf").click(function() {
            var from = $.trim($("#from").val());
            var to = $.trim($("#to").val());
            var branch = $("#branch_id").val();
            var client = $("#client_id").val();
            var type = $("#type").val();
            var pdf = this.id === 'export-pdf' ? '&pdf=1' : '';
            
            if(from && to) window.open(`{{ route('tenant.payment.export', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}&client_id=${client}&type=${type}${pdf}`, '_blank');
        });
    });
</script>
@stop