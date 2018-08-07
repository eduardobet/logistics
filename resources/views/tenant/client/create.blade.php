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

        <div class="section-wrapper">
            {!! Form::open(['route' => ['tenant.client.store', $tenant->domain]]) !!}
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" name="branch_code" value="{{ $branch->code }}">
                {!! Form::hidden('branches[]', $branch->id) !!}
            
                @include('tenant.client._fields', [
                    'departments' => [],
                    'zones' => [],
                    'client' => new Logistics\DB\Tenant\Client,
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
@endsection