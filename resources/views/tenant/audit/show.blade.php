@extends('layouts.tenant')

@section('title')
  {{ __('Audits') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
     <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

            @can('create-invoice')
                <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
                    <i class="fa fa-plus mg-r-5"></i> {{ __('Create') }}
                </a>
            @endcan
          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">

        <div class="table-responsive-sm" id="app">
            
           @foreach ($audits as $audit)
                <div id="data" data-metadata='{!! $audit->getMetadata(true) !!}' data-modified='{!! $audit->getModified(true) !!}'>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.id')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.audit_id }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.event')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.audit_event }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.user')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.full_name }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.ip_address')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.audit_ip_address }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.user_agent')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.audit_user_agent }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <strong>@lang('common.url')</strong>
                        </div>
                        <div class="col-md-9">@{{ metadata.audit_url }}</div>
                    </div>
                </div>

                <hr/>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>@lang('common.attribute')</th>
                            <th>@lang('common.old')</th>
                            <th>@lang('common.new')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(value, attribute) in modified">
                            <td><strong>@{{ attribute }}</strong></td>
                            <td>
                                <span class="badge badge-danger">@{{ value.old }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">@{{ value.new }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
           @endforeach
        
        </div>

     </div><!-- container -->

</div><!-- slim-mainpanel -->

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>
    <script>
        Vue.config.devtools = true;
        new Vue({
            el: '#app',
            data: {
                metadata: JSON.parse($('#data').attr('data-metadata')  || '{}' ),
                modified: JSON.parse($('#data').attr('data-modified')  || '{}' )
            }
        });

    </script>
@endsection