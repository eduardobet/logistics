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

            <div class="row">
                <div class="col-12">
                    <h5>
                        {{ __('Invoice') }}
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        @if ($payment->invoice->is_paid)
                            <span class="badge badge-success"><small>{{ __('Paid') }}</small></span>
                        @else
                            <span class="badge badge-danger"><small>{{ __('Pending') }}</small></span>
                        @endif
                    </h5>
                </div>
            </div>

            <div class="row">
                
                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">#:</label>
                        <input type="text" value="{{ $payment->invoice->branch->initial }}-{{ $payment->invoice->manual_id_dsp }}" class="form-control" readonly>
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Type') }}:</label>
                        
                        @if ($payment->invoice->warehouse)
                            <input type="text" value="Warehouse" class="form-control" readonly>
                        @else    
                            <input type="text" value="Internet" class="form-control" readonly>
                        @endif
                        
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Total') }}:</label>
                        <input type="text" value="$ {{ number_format($payment->invoice->total, 2) }}" class="form-control" readonly>
                    </div>    
                </div>
                
             </div>

         </div><!--section-wrapper--> 

        <br>
        
        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">       

            <div class="row">
                <div class="col-12">
                    <h5>{{ __('Payment') }} </h5>
                </div>
            </div>

            <div class="row">
            
                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">#:</label>
                        <input type="text" value="{{ $payment->id }}" class="form-control" readonly>
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Payment method') }}:</label>
                        
                        {!! Form::select('payment_method', [1 => __('Cash'), 2 => __('Wire transfer'), 3 => __('Check'), ], $payment->payment_method, ['class' => 'form-control', 'id' => 'payment_method',]) !!}
                        
                    </div>    
                </div>

                <div class="col-4">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Total') }}:</label>
                        <input type="text" value="$ {{ number_format($payment->amount_paid, 2) }}" class="form-control" readonly>
                    </div>    
                </div>
                
             </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mg-b-10-force">
                        <label class="form-control-label">{{ __('Reference') }}:</label>
                        <input type="text" value="{{ $payment->payment_ref }}" class="form-control" readonly>
                    </div>
                </div>
            </div>

            @can('create-payment')
                <div class="row">
                    <div class="col-lg-12 mg-t-25">
                        @if ($payment->status == 'A')
                        <button class="btn btn-primary  bd-1 bd-gray-400 terminate" id="edit-payment" data-pm="{{ $payment->payment_method }}">
                            {{ __('Editing :what', ['what' => __('Payment method') ]) }}
                        </button>
                         @endif

                        @can('delete-payment')
                            
                            @if ($payment->status == 'A')
                                <button id="btn-delete" class="btn btn-danger terminate"
                                data-loading-text="<i class='fa fa-spinner fa-spin '></i> ..."
                                data-status="I" 
                                >
                                {{ strtoupper( __('Inactivate') ) }}
                                </button>
                            @else 
                            {{--
                                <button id="btn-delete" class="btn btn-danger terminate"
                                data-loading-text="<i class='fa fa-spinner fa-spin '></i> ..."
                                data-status="A" 
                                >
                                {{ strtoupper( __('Activate') ) }}
                                </button>
                            --}}
                            @endif

                        @endcan

                    </div>
                </div>
            @endcan
          </div>
 
          <!--section-wrapper-->

          <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                @if ($payment->creator)
                    <p>{{ __('Created by') }} <b>{{ $payment->creator->full_name }}</b> | <b>{{ $payment->created_at->format('d/m/Y') }}</b> | {{ $payment->created_at->format('g:i A') }} </p>
                @endif    
                @if ($payment->editor)
                    <p>{{ __('Edited by') }} <b>{{ $payment->editor->full_name }}</b> | <b>{{ $payment->updated_at->format('d/m/Y') }}</b> | {{ $payment->updated_at->format('g:i A') }} </p>
                @endif
                
            </div>

            <div class="col-lg-12">
                <ul>
                    @forelse ($payment->audits as $audit)
                    <li>
                        @lang('payment.updated.metadata',array_except( $audit->getMetadata(), ['user_permissions']))

                        @foreach ($audit->getModified() as $attribute => $modified)
                        <ul>
                            <li>
                                {!! __('payment.'.$audit->event.'.modified.'.$attribute, $modified) !!} 
                            </li>
                        </ul>
                        @endforeach
                    </li>
                    @empty
                    <p>@lang('payment.unavailable_audits')</p>
                    @endforelse
                </ul>
            </div>


          </div>
    
    </div> <!-- container -->     
</div> <!-- slim-mainpanel -->


@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
<script>
    @can('create-payment')
    $(function() {
        $("#edit-payment").click(function(e){
            var self = $(this);
            var savedPaymentMethod = self.data('pm');
            var paymentMethod = $("#payment_method").val();

            if (savedPaymentMethod != paymentMethod) {
                self.prop('disabled', true);

                var request = $.ajax({
                    method: 'post',
                    url: "{{ route('tenant.payment.update', $tenant->domain) }}",
                    data: $.extend({
                        _token	: $("meta[name='csrf-token']").attr('content'),
                        '_method': 'PATCH',
                        'payment_id': "{{ $payment->id }}",
                        'payment_method': paymentMethod,
                    }, {})
                });

                request.done(function(data){
                    if (data.error == false) {
                        swal("", data.msg, "success").then(function() {
                            window.location.reload(true);
                        });
                    } else {
                        swal("", data.msg, "error");
                        self.prop('disabled', false);
                    }
                })
                .fail(function( jqXHR, textStatus ) {
                    
                    var error = "{{ __('Error') }}";

                    if (jqXHR.responseJSON.msg) {
                        error = jqXHR.responseJSON.msg;
                    }
                    
                    swal("", error, "error");
                    self.prop('disabled', false);
                });
             }
        });
    });
    @endcan

    @can('delete-payment')
    $(function() {
        $("#btn-delete").click(function(e) {
            var self = $(this);
            var status = self.data('status');
            swal({
                title: '{{__("Are you sure") }}?',                    
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: '{{ __("No") }}',
                confirmButtonText: '{{ __("Yes") }}'
            })
            .then((result) => {
                if (result.value) {
                    inactivate(self, status);
                }
            });
        });
    });

    function inactivate($btnSubmit, status) {
        $(".terminate").prop('disabled', true);
        var loadingText = $btnSubmit.data('loading-text');

        if ($btnSubmit.html() !== loadingText) {
            $btnSubmit.data('original-text', $btnSubmit.html());
            $btnSubmit.html(loadingText);
        }

        swal({
            title: '{{__("Please indicate why you are :doing the :what", ["doing" => ($payment->status == "A" ? __("inactivating") : __("activating") ), "what" => __("Payment"), ]) }}',
            input: 'textarea',
            showCancelButton: true,
            inputValidator: (value) => {
                return !value && '{{ __("Error") }}!'
            }
        }).then((result) => {
            if (notes = result.value) {
                var request = $.ajax({
                    method: 'post',
                    url: "{{ route('tenant.payment.update', $tenant->domain) }}",
                    data: $.extend({
                        _token	: $("meta[name='csrf-token']").attr('content'),
                        '_method': 'PATCH',
                        'notes': notes,
                        'status': status,
                        'toggling': 'Y',
                        'payment_id': "{{ $payment->id }}",
                    }, {})
                });

                request.done(function(data){
                    if (data.error == false) {
                        swal("", data.msg, "success");
                    } else {
                        swal("", data.msg, "error");
                    }
                    $btnSubmit.html($btnSubmit.data('original-text'));
                })
                .fail(function( jqXHR, textStatus ) {
                    
                    var error = "{{ __('Error') }}";

                    if (jqXHR.responseJSON.msg) {
                        error = jqXHR.responseJSON.msg;
                    }
                    
                    swal("", error, "error");
                    $(".terminate").prop('disabled', false);
                    $btnSubmit.html($btnSubmit.data('original-text'));
                });
            }
        });
    }
    @endcan
</script>    
@endsection