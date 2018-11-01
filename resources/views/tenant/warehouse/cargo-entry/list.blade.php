@extends('layouts.tenant')

@section('title')
  {{ __('Cargo entries') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
           {{ Breadcrumbs::render() }}

             <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.warehouse.cargo-entry.create', $tenant->domain) }}">
                <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
             </a>
          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

            
            <div class="row mg-b-10">
                
                <div class="col-lg-3">
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('From') }}
                        </div>
                    </div>
                    <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('from', date('Y-m-d')) }}" id="from">
                </div>
                
            </div>
            
            <div class="col-lg-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ __('To') }}
                        </div>
                    </div>
                    <input type="text" class="form-control fc-datepicker hasDatepicker" placeholder="YYYY-MM-DD" value="{{ request('to', date('Y-m-d')) }}" id="to">
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="input-group">
                    <select name="branch_id" id="branch_id" class="form-control select2" style="width: 100%">
                        <option value="">{{ __('Branch') }}</option>
                        @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == $branch->id ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-lg-2">
                <div class="input-group">
                    <button class="btn" type="button" id="filter">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            
        </div>
        
        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0 pdf-table">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Branch') }}</th>
                        <th>{{ __('Created by') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach ($cargo_entries as $cargo_entry)
                    <tr>
                        <th scope="row">{{ $cargo_entry->id }}</th>
                        <td>{{ $cargo_entry->created_at->format('d/m/Y g:i A') }}</td>
                            <td>{{ $cargo_entry->branch->name }}</td>
                            <td>{{ $cargo_entry->creator->full_name }}</td>
                            <td>
                                @if (!$cargo_entry->type || $cargo_entry->type == 'N' )
                                    <label class="badge badge-success">
                                        {{ __('Normal') }}
                                    </label>
                                @elseif($cargo_entry->type == 'M')
                                    
                                    <label class="badge badge-danger">
                                        {{ __('Misidentified') }}
                                    </label>
                                @endif
                            </td>
                            <td class="text-center">
                                <a target="_blank" title="{{ __('View') }}" href="{{ route('tenant.warehouse.cargo-entry.show', [$tenant->domain, $cargo_entry->id]) }}"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        
                    </tbody>
                </table>
                
                @if ($searching == 'N')
                <div id="result-paginated" class="mg-t-25">
                    {{ $cargo_entries->links() }}
                </div> 
                @endif
            </div>
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
            window.location = `{{ route('tenant.warehouse.cargo-entry.list', $tenant->domain) }}?from=${from}&to=${to}&branch_id=${branch}`;
        });
    });
</script>
@stop
