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
            {!! Form::model($client, ['route' => ['tenant.client.update', $tenant->domain, $client->id], 'method' => 'PATCH', ]) !!}
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <input type="hidden" name="branch_code" value="{{ $branch->code }}">
            @include('tenant.client._fields', [
                'departments' => $departments->toArray(),
                'zones' => $zones->toArray(),
                'mode' => 'edit',
            ])
            </form>
         </div>

         <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                @if ($client->creator)
                    <p>{{ __('Created by') }} <b>{{ $client->creator->full_name }}</b> | <b>{{ $client->created_at->format('d/m/Y') }}</b> | {{ $client->created_at->format('g:i A') }} </p>
                @endif    
                @if ($client->editor)
                    <p>{{ __('Edited by') }} <b>{{ $client->editor->full_name }}</b> | <b>{{ $client->updated_at->format('d/m/Y') }}</b> | {{ $client->updated_at->format('g:i A') }} </p>
                @endif
                
            </div>
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