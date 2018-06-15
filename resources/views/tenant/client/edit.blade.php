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

    <script>
        var ecCache = {};
        $(function() {
            var $container = $("#details-container");
		    var index = $container.find(".det-row").length + 1;

            $(".btn-add-more").click(function() {
                var $self = $(this);
                var url = $self.data('url');
                var loadingText = $self.data('loading-text');

                if (view = ecCache.data) {
                    add(view)
                    return;
                }

                if ($(this).html() !== loadingText) {
                    $self.data('original-text', $(this).html());
                    $self.prop('disabled', true).html(loadingText);
                }

                $.getJSON(url, function(data) {
                    $self.prop('disabled', false).html($self.data('original-text'));
                    ecCache['data'] = data.view;
                    add(data.view)
                });

                console.log(ecCache);

                index++;
            });

            function add(view) {
                $container.append(view);
            }
        });
    </script>
@endsection