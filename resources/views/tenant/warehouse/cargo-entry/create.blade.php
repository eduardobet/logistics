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

         <div class="section---wrapper">
            {!! Form::open(['route' => ['tenant.warehouse.cargo-entry.store', $tenant->domain], 'name' => 'frm-cargo-entry', 'id' => 'frm-cargo-entry', ]) !!}

            @include('tenant.common._notifications')

            <div class="row row-xs">
                <div class="col-sm-12">
                    <div class="card card-status">
                        <div class="form-group mg-b-20-force">
                        <h4 class="tx-bold tx-inverse">{{ strtoupper(__('Tracking numbers')) }} (<span id="qty-dsp"></span>)<span class="tx-danger">*</span>

                            <label class="badge badge-success" style="display:none" id="sisyphus-indicator-saving">
                                <small><em>{{ __('Saving') }}...</em></small>
                            </label>
                            <label class="badge badge-warning" style="display:none" id="sisyphus-indicator-restoring">
                                <small><em>{{ __('Restoring') }}...</em></small>
                            </label>
                        </h4>
                        
                        {!! Form::textarea('trackings', null, ['rows' => 14, 'class' => 'form-control mg-b-6-force', 'required' => 1, 'id' => 'trackings', ]) !!}

                    </div>
                    
                    <div class="form-group mg-b-20-force">
                        <label for="type" class="form-label">{{ __('Type') }}</label>
                         {!! Form::select('type', ['N' => __('Normal'), 'M' =>  __('Misidentified') ], old('type', request('type')), ['class'=> 'form-control', 'id' => 'type',]) !!}
                    </div>
                    
                    
                    <div class="form-group mg-b-0-force">
                        <button class="btn btn-primary" type="submit"><b>{{ __('Save') }}</b></button><br>
                    </div>

                    </div><!-- card -->
                </div><!-- col-sm-12 -->

            </div>
                
                <input type="hidden" id="branch_id" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" id="qty" name="qty" value="">
            </form>
         </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection


@section('xtra_scripts')
    <script>
    $(function() {
        // counter
        $("#trackings").keyup(function(e) {
            if (e.keyCode == 13){
                if (trackings = $.trim(this.value) ) {
                    countTracking(trackings);
                }
            }
        });

        $("#trackings").blur(function(e) {
            if (trackings = $.trim(this.value)) {
                countTracking(trackings);
            } else {
                $("#qty").val('');
                $("#qty-dsp").text(0) 
            }
        });

        countTracking($.trim($("#trackings").val()));

        var $sisyphus = $( "#frm-cargo-entry" ).sisyphus({
            excludeFields: $("input[name='_token']"),
            onSave: function(){
                $("#sisyphus-indicator-saving").show().fadeOut(2e3);
            }
            ,onRestore: function(){
                $("#sisyphus-indicator-restoring").show().fadeOut(2e3);
                countTracking($.trim($("#trackings").val()));
            }
        });

        // 
        $("#type").change(function() {
            var url = "{{ route('tenant.warehouse.cargo-entry.create', [$tenant->domain, ]) }}?type="+this.value;
            if (!$.trim( $("#trackings").val() )) window.location = url
            else {
                swal({
                    title: '{{__("Are you sure") }}?',
                    text: "{{ __('All unsaved data will be lost') }}!",
                    
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: '{{ __("No") }}',
                    confirmButtonText: '{{ __("Yes") }}'
                })
                .then((result) => {
                    if (result.value) {
                        if ($sisyphus) $sisyphus.manuallyReleaseData();
                        window.location = url;
                    }
                });
            }
         });

     });
    
    function countTracking(trackings) {
        var qty = !trackings ? 0 : (trackings.match(/\r?\n/g) || '').length + 1;
        $("#qty").val( qty );
        $("#qty-dsp").text(qty);
    }
    </script>
@stop
