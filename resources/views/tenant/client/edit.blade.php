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
            {!! Form::model($client, ['route' => ['tenant.client.update', $client->id], 'method' => 'PATCH', ]) !!}
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <input type="hidden" name="branch_code" value="{{ $branch->code }}">
            @include('tenant.client._fields', [
                'departments' => $departments->toArray(),
                'zones' => $zones->toArray(),
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
@endsection