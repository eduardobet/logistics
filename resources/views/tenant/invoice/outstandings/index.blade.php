@extends('layouts.tenant')

@section('title')
  {{ __('Clients') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
      <div class="container">

        <div class="slim-pageheader">
          
           {{ Breadcrumbs::render() }}

           @can('create-client')
              <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.client.create', $tenant->domain) }}">
                <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
              </a>
            @endcan

          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="row mg-b-10">
            
            <div class="col-lg-10">
                <div class="input-group">
                    <select name="branch_id" id="branch_id" class="form-control select2" style="width: 100%">
                        <option value="">--{{ __('Branch') }}--</option>
                        @foreach ($branches as $aBranch)
                        <option value="{{ $aBranch->id }}"{{ $aBranch->id == request('branch_id', $branch->id) ? " selected" : null }}>
                            {{ $aBranch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-lg-2">
                <div class="input-group">
                    <button class="btn" type="button" id="btn-filter">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            
        </div>

        <div class="table-responsive-sm">
            <table class="table table-hover mg-b-0">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('First Name') }}</th>
                  <th>{{ __('Last Name') }}</th>
                  <th>{{ __('Box') }}</th>
                  <th>{{ __('Email') }}</th>
                  <th>{{ __('Telephones') }}</th>
                  <th>{{ __('Total') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                  
                  @foreach ($clients as $client)
                      
                    <tr>
                      <th scope="row">{{ $client->manual_id_dsp }}</th>
                      <td>{{ $client->first_name }}</td>
                      <td>{{ $client->last_name }}</td>
                      <td>{{ $client->branch ? $client->branch->code : null }}{{ $client->manual_id_dsp }}</td>
                      <td>{{ $client->email }}</td>
                      <td>{{ $client->telephones }}</td>
                      <td>${{ number_format($client->clientInvoices->sum('pending'), 2) }}</td>
                      <td class="text-center">
                        @can('show-invoice')
                          <a href="{{ route('tenant.outstandings.details', [$tenant->domain, 'branch_id' => request('branch_id', $branch->id), 'client_id' => $client->id, ]) }}"><i class="fa fa-eye"></i></a>
                        @endcan

                      </td>
                    </tr>

                    @if($loop->last)
                        <tr>
                            <td colspan="6" style="text-align: right">Total:</td>
                            <td>$ {{ number_format($client->clientInvoices->sum('pending'), 2)  }}</td>
                            <td></td>
                        </tr>
                    @endif

                @endforeach
               
              </tbody>
            </table>

            <div id="result-paginated" class="mg-t-25">
                <hr>
                {{ $clients->appends(['branch_id' => request('branch_id'), 'client_id' => request('client_id') ])->links() }}
            </div>

          </div>
        
        </div>

      </div><!-- container -->
</div><!-- slim-mainpanel -->


 @include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
      $(function() {
        // searching
        $("#branch_id").select2({width: 'resolve', allowClear: true, 'placeholder': "{{ __('Branch') }}"});
        $("#branch_id").change();

        $("#btn-filter").click(function() {
            var branch = $("#branch_id").val();
            window.location = `{{ route('tenant.outstandings.list', $tenant->domain) }}?branch_id=${branch}`;
        });

      });
    </script>
@endsection