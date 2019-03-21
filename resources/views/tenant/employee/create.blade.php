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
            {!! Form::open(['route' => ['tenant.admin.employee.store', $tenant->domain]]) !!}
            
                @include('tenant.employee._fields', [
                    'status' => ['L' => __('Lock'),]
                ])
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    <script>
        var cache = {};
        $(function() {
            $("#in_branch").select2({width: 'resolve'});

             // filter permissins
            $("#filter").keyup(delay(function() {
                var term = $.trim(this.value);

                $(".card").each(function (index) {
                    //if (!term) return;
                    $(this).find(".section-title").each(function () {
                        var id = $(this).text().toLowerCase().trim();
                        var not_found = (id.indexOf(term) == -1);
                        $(this).closest('.card').toggle(!not_found);
                        return not_found;
                    });
                });
                
            }, 500));
        });

        function delay(callback, ms) {
            var timer = 0;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                callback.apply(context, args);
                }, ms || 0);
            };
        }
    </script>
@stop