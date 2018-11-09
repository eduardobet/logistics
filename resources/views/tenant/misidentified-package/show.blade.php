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
        <h4 class="slim-logo"><a href="/">Seal<span>.</span>Log</a></h4>
      </div>
      <div class="slim-header-right">
        <h5 class="tx-reef">{{ strtoupper( __('Misidentified packages') ) }} # {{ request('id') }} </h5>
        <!-- dropdown -->
      </div>

    </div>
  </div>
  <!-- slim-header -->
  <div class="container">

    <div class="slim-mainpanel">

             <div class="row row-xs">
                <div class="col-sm-12">
                    <div class="card card-status">
                        <div class="form-group mg-b-20-force">
                            <h4 class="tx-bold tx-inverse">{{ strtoupper(__('Tracking numbers')) }} (<span id="qty-dsp">{{ count(explode(PHP_EOL, $misidentified_package->trackings)) }}</span>)<span class="tx-danger">*</span>
                            </h4>
                            
                            {!! Form::textarea('trackings', $misidentified_package->trackings, ['rows' => 10, 'class' => 'form-control mg-b-6-force', 'required' => 1, 'id' => 'trackings', 'readonly' => 1, ]) !!}

                         </div>

                         <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mg-b-20-force">
                                    <label for="branch_to" class="form-label">{{ __('Destination branch') }}</label>
                                    {!! Form::select('branch_to', [$misidentified_package->toBranch->id => $misidentified_package->toBranch->name],$misidentified_package->branch_to, ['class'=> 'form-control', 'id' => 'branch_to', 'required' => 1, 'disabled' => 1, ]) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mg-b-20-force">
                                    <label for="client_id" class="form-label">ID {{ __('Client') }}</label>
                                {!! Form::text('client_id', $misidentified_package->client_id, ['class' => 'form-control', 'id' => 'client_id', 'readonly']) !!}
                                </div>
                            </div>
                                
                            <div class="col-md-2">
                                <div class="form-group mg-b-20-force">
                                    <label for="cargo_entry_id" class="form-label">ID {{ __('Cargo entry') }}</label>
                                {!! Form::text('cargo_entry_id', $misidentified_package->cargo_entry_id, ['class' => 'form-control', 'id' => 'cargo_entry_id', 'readonly']) !!}
                                </div>
                            </div>

                         </div>

                    </div><!-- card -->
                 </div><!-- col-sm-12 -->
             </div><!-- row -->
                
            <input type="hidden" id="qty" name="qty" value="">
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
</body>
</html>