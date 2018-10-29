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
  <div class="slim-header">
    <div class="container">
      <div class="slim-header-left">
        <h4 class="slim-logo"><a href="/">Seal<span>.</span>Log</a></h4>
      </div>
      <div class="slim-header-right">
        <h5 class="tx-reef">{{ strtoupper( __('Packages tracking') ) }}</h5>
        <!-- dropdown -->
      </div>

    </div>
  </div>
  <!-- slim-header -->
  <div class="container">

    <div class="slim-mainpanel">

    <form action="{{ route('tenant.tracking.post', [$tenant->domain]) }}" method="POST">
        
        <div class="section-wrapper mg-t-20 mg-b-20">
            <div class="col-lg-12">
                <div class="input-group">
                    <input id="q-tracking" name="q-tracking" type="text" class="form-control form-control-lg" placeholder="{{ __('Enter a tracking number and press enter or click the button') }}">

                    @captcha("{{ config('app.locale') }}")

                    <span class="input-group-btn">
                        <button type="submit" class="btn bg-mantle tx-white bd-gray-500 btn-lg" type="button"><i class="fa fa-search"></i></button>
                    </span>
                </div><!-- input-group -->
            </div>
            
        </div><!-- section-wrapper -->

    </form>


      @include('tenant.tracking.status')


      <div class="alert alert-solid alert-danger mg-t-20 d-none" role="alert" id="misidentified-container">
            <strong>{{ __('Misidentified') }}!</strong> {{ __('This package is misidentified, please contact the company for more details') }}.
      </div>

      <div class="section-wrapper mg-t-20 mg-b-20 d-none" id="status-box-info">
        <h3 class="tx-primary" id="info-term">9872349238472398472394</h3>
        <p class="mg-b-0 mg-sm-b-0" id="info-ubication"><i class="fa fa-map-marker"></i> Ubicacion: Los Andes</p>
        <p class="mg-b-0 mg-sm-b-0" id="info-date"><i class="fa fa-calendar"></i> Fecha: 00/00/0000 / Hora: 00:00Pm</p>
      </div><!-- section-wrapper -->

    </div>
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
  
  <script type="text/javascript">
     _submitEvent = function() {
        $.ajax({
            type: "POST",
            url: "{{ route('tenant.tracking.post', [$tenant->domain]) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "term": $("#q-tracking").val(),
                "g-recaptcha-response": $("#g-recaptcha-response").val()
            },
            dataType: "json",
            success: function(resp) {
                $('#g-recaptcha-response').html(resp.token)
                console.log('resp = ', resp)

                markFirstBox(resp.data);
                markSecondBox(resp.data);
                markThirdBox(resp.data, resp.data.las);
                markMisidentified(resp.data.mReca);
            },
            error: function(error) {
                console.log('error = ', error);
            }
        });
      };

      function markFirstBox(data)
      {
        if (!data) return;

        var firstReca = data.recas[0];
        if (firstReca) {
          infoize(firstReca, 'first', 'tx-purple');
        }
      }

      function markSecondBox(data)
      {
        if (!data) return;
        var secondReca = data.recas[1];
        if (secondReca) {
          infoize(secondReca, 'second', 'tx-warning');
        }
      }

      function markThirdBox(data)
      {
        if (!data) return;

        if (data.recas[1] && data.last_wh && !data.mReca) {
          infoize(data.last_wh, 'third', 'tx-primary');
        }
      }

      function markFourthBox(data)
      {
        if (!data) return;

        if (!data.mReca) {
          infoize(secondReca, 'fourth', 'tx-danger');
        }
      }

      function markFifthBox(recas, invoice, mReca)
      {
        var secondReca = recas[1];
        if (secondReca && invoiced && !data.mReca) {
          infoize(secondReca, 'fifth', 'tx-success');
        }
      }

      function markMisidentified(mReca)
      {
        if (mReca) {
          $("#misidentified-container").toggleClass('d-none');
        }
      }

      function resetter(){}

      function infoize(data, number, highlightclass)
      {
        var branch = data.branch || data.to_branch;

        if (number == 'third') console.log(data, number, highlightclass)

        $("#"+number+"-icon").removeClass('tx-gray-300').addClass(highlightclass); 
         $("#"+number+"-title").removeClass('tx-gray-300');
         $("#"+number+"-description").removeClass('tx-gray-300');
         $("#"+number+"-localization").removeClass('tx-gray-300').html(`<i class="fa fa-map-marker"></i> ${branch.name}`);
         $("#"+number+"-date").removeClass('tx-gray-300').html(`<i class="fa fa-map-calendar"></i> ${data.created_at_dsp}`);
      }
    </script>
</body>
</html>