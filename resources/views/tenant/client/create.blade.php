@extends('layouts.tenant')

@section('title')
  {{ __('Dashboard') }}  {{ config('app.name', '') }}
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
            {!! Form::open(['route' => ['tenant.client.store', $tenant->domain]]) !!}
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" name="branch_code" value="{{ $branch->code }}">
                <input type="hidden" name="branch_initial" value="{{ $branch->initial }}">
                {!! Form::hidden('branches[]', $branch->id) !!}

                @if ($tenant->migration_mode)
                    <div class="row">
                        <div class="col-lg-12">
                            <h4>
                                <label class="badge badge-danger">{{ __('Migration mode') }}...</label>
                            </h4>
                        </div>
                    </div>
                @endif

                @include('tenant.client._fields', [
                    'departments' => [],
                    'zones' => [],
                    'client' => new Logistics\DB\Tenant\Client,
                    'mode' => 'create',
                ])
            </form>
        </div>

    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    @include('common._select2ize')
    @include('common._add_more')
    @include('common._toogle-for-text')

    <script>
      $(function() {
          $("input[name='email']").blur(function() {
              if ($.trim(this.value)) this.value = this.value.toLowerCase();
          });
      });
    </script>  
@endsection