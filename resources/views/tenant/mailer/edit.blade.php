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
            @include('tenant.common._notifications')

            {!! Form::model(new \Logistics\DB\Tenant\Mailer, ['route' => ['tenant.mailer.update', $tenant->domain, request('id')], 'method' => 'PATCH', ]) !!}

            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">

            <div class="mg-t-25">
                <button class="btn btn-sm btn-outline-success btn-add-more" type="button"
                data-url="{{ route('tenant.mailer.mailer-tpl', $tenant->domain) }}"
                data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{ __('Loading') }}..."
                >
                    <i class="fa fa-plus"></i> {{ __('Add') }}
                </button>
            </div>

            <div id="details-container">
                <div class="mg-t-25"></div>
                
                @foreach ($mailers as $key => $mailer)
                    @include('tenant.mailer.mailer-tmpl', [
                        'key' => $key,
                        'mailer' => $mailer,
                    ])
                @endforeach
                
            </div>

            <div class="row mg-t-25 justify-content-between">
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-primary  bd-1 bd-gray-400">{{ __('Save') }}</button>
                </div>
            </div>
            
            </form>
        </div>

    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    @include('common._add_more')
@endsection