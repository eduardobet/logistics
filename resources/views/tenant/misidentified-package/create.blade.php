<!DOCTYPE html>
<!--seal control panel-->
<!--code by Josue Artaud "thebrain"-->
<!--theme slim mod by Eduardo Betancourt-->
<!--project for seal logistic miami-panama-->
<html lang="{{ config('app.locale') }}">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Meta -->
  <meta name="description" content="Seal Control Panel.">
  <meta name="author" content="Josue Artaud and Eduardobet">

  <title>Seal Logistics Control Panel V1.0</title>

  <link href="{{ mix('css/tenant.css') }}" rel="stylesheet">

  <style>
      .slim-footer{padding: 10px;}
      .slim-footer .container {
    min-height: 60px !important;}
  </style>

</head>

<body>
  <div class="noty-container"></div>  

  <div class="slim-header">
    <div class="container">
      <div class="slim-header-left">
        <h4 class="slim-logo">
            @if ($user->isClient())
                <a href="{{ route('tenant.warehouse.list', [$tenant->domain]) }}">{{ config('app.name') }}</a>
            @else
                <a href="/">{{ config('app.name') }}</a>
            @endif
        </h4>
      </div>
      <div class="slim-header-right">
        <h5 class="tx-reef">{{ strtoupper( __('Create misidentified package') ) }}</h5>
        <!-- dropdown -->
      </div>

    </div>
  </div>
  <!-- slim-header -->
  <div class="container">

    <div class="slim-mainpanel">

        {!! Form::open(['route' => ['tenant.misidentified-package.store', $tenant->domain], 'name' => 'frm-misidentified', 'id' => 'frm-misidentified', ]) !!}

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
                            
                            {!! Form::textarea('trackings', null, ['rows' => 10, 'class' => 'form-control mg-b-6-force', 'required' => 1, 'id' => 'trackings', ]) !!}

                         </div>

                         <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mg-b-20-force">
                                    <label for="branch_to" class="form-label">{{ __('Destination branch') }}</label>
                                    {!! Form::select('branch_to', $branches->prepend('----', ''), old('branch_to', request('branch_to')), ['class'=> 'form-control', 'id' => 'branch_to', 'required' => 1, ]) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mg-b-20-force">
                                    <label for="client_id" class="form-label">ID {{ __('Client') }}</label>
                                {!! Form::text('client_id', $user->isClient() ? $user->client_id : null, ['class' => 'form-control', 'id' => 'client_id'] + ( $user->isClient() ? [' readonly' => 1] : []   )   ) !!}
                                </div>
                            </div>
                                
                            <div class="col-md-2">
                                <div class="form-group mg-b-20-force">
                                    <label for="cargo_entry_id" class="form-label">ID {{ __('Cargo entry') }}</label>
                                {!! Form::text('cargo_entry_id', null, ['class' => 'form-control', 'id' => 'cargo_entry_id']+ ( $user->isClient() ? [' readonly' => 1] : []   )) !!}
                                </div>
                            </div>

                         </div>
                                            
                        <div class="form-group mg-b-0-force mg-t-10-force">
                            <button class="btn btn-primary" type="submit" id="btn-misidentified"><b>{{ __('Send') }}</b></button><br>
                        </div>

                    </div><!-- card -->
                 </div><!-- col-sm-12 -->
             </div><!-- row -->
                
            <input type="hidden" id="qty" name="qty" value="">
        </form>
    </div><!-- slim-mainpanel -->
  </div><!-- container -->

  <div class="slim-footer">
    <div class="container">
      <p>
          {!! $tenant->telephones_dsp !!} <br>
          </i> {!! $tenant->emails_dsp !!} <br><br>
          &copy; {{ __('Copyright :year | :company', [
            'year' => date('Y'),
            'company' => config('app.name')
        ])  }}
      </p>

    </div>
    <!-- container -->
  </div>

  <script src="{{ mix('js/app.js') }}"></script>
  
  <script>
    var $sisyphus;
    $(function() {

        // TOdo: remove
        $("#frm-misidentified").submit(function(e) {
            if (this.checkValidity()) _submitEvent();
            e.preventDefault();
        });

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

        $sisyphus = $( "#frm-misidentified" ).sisyphus({
            excludeFields: $("input[name='_token']"),
            onSave: function(){
                $("#sisyphus-indicator-saving").show().fadeOut(2e3);
            }
            ,onRestore: function(){
                $("#sisyphus-indicator-restoring").show().fadeOut(2e3);
                countTracking($.trim($("#trackings").val()));
            }
        });

    });
        
    function countTracking(trackings) {
        var qty = !trackings ? 0 : (trackings.match(/\r?\n/g) || '').length + 1;
        $("#qty").val( qty );
        $("#qty-dsp").text(qty);
    }

    _submitEvent = function() {
        var $btnSubmit = $("#btn-misidentified");
        $btnSubmit.prop('disabled', true).html("<i class='fa fa-spinner fa-spin'></i>")
        
        $.ajax({
            type: "POST",
            url: "{{ route('tenant.misidentified-package.store') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "trackings": $.trim($("#trackings").val()),
                "branch_to": $("#branch_to").val(),
                "client_id": $.trim($("#client_id").val()),
                "cargo_entry_id": $.trim($("#cargo_entry_id").val()),
            },
            dataType: "json",
            success: function(resp) {
                $btnSubmit.prop('disabled', false).html('{{ __("Send") }}');

                if ($sisyphus) $sisyphus.manuallyReleaseData();

                swal('', resp.msg, 'success');

                $("#trackings").val("");
                $("#branch_to").val("");
                @if (!$user->isClient())
                    $("#client_id").val("");
                @endif
                $("#cargo_entry_id").val("");
            },
            error: function(error) {
              var eObj = error.responseJSON.msg ? error.responseJSON.msg : (error.responseJSON.errors ? error.responseJSON.errors : null);
              var _error = eObj ? eObj : "{{ __('Error') }}";
              swal('', _error, 'error');

              $btnSubmit.prop('disabled', false).html('{{ __("Send") }}');
            }
        });
      };
</script>
</body>
</html>