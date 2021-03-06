<div class="row row-xs mg-t-10">

    <div class="col-lg-12 col-xl-12">

        <div class="row row-xs">

            <div class="col-md-4 col-lg-4 col-xl-4">
                <div class="card card-activities pd-20">
                  <h6 class="slim-card-title">{{ __('Recent activities') }}</h6>
                  <p>Ultimo Ingreso fue hace 1 Hora</p>

                  <div class="media-list">
                    
                    
                    @foreach ($branch->notifications->take(5) as $notif)
                      <div class="media">
                        <div class="activity-icon {{ array_random(['bg-primary', 'bg-success', 'bg-purple', 'bg-warning', 'bg-info']) }}">
                          <i class="icon ion-ios-information-outline"></i>
                        </div><!-- activity-icon -->
                        <div class="media-body">
                          <h6>{{ $notif['data']['title'] }}</h6>
                          <p>{{ $notif['data']['content'] }}</p>
                          
                          <span>{{ do_diff_for_humans($notif['data']['created_at']['date']) }}</span>
                        </div><!-- media-body -->
                      </div><!-- media -->

                    @endforeach

                  </div><!-- media-list -->

                </div><!-- card -->
              </div><!-- col-5 -->

              <div class="col-md-4 col-lg-4 col-xl-4 mg-t-10 mg-md-t-0">
                  <div class="card card-people-list pd-20">
                    <div class="slim-card-title">{{ __('Today profits') }}</div>
                    <div class="media">
                        <div class="media-body">
                            @can('show-today-earning')
                              <h1>$ {{ number_format($today_earnings, 2) }}</h1>
                            
                              <a href="{{ route('tenant.payment.list', [$tenant->domain, 'branch_id' => $branch->id, 'from' => date('Y-m-d'), 'to' => date('Y-m-d') ]) }}" class="statement-link"> {{ __('Show movements') }} <i class="fa fa-angle-right mg-l-5"></i></a>
                            @endcan
                        </div>
                    </div>
                  </div><!-- card -->
              </div><!-- col-7 -->
              
<div class="col-lg-4 col-xl-4 mg-t-10 mg-lg-t-0">
        <div class="card card-people-list pd-20">
            <div class="slim-card-title">{{ __('Last clients') }}</div>
                <div class="media-list">
                  
                    @foreach ($last_5_clients as $lclient)
                      <div class="media">
                          <img src="{{ asset('images/200x200.png') }}" alt="">
                          <div class="media-body">
                          <a href="{{ route('tenant.client.edit', [$tenant->domain, $lclient->id]) }}">{{ $lclient->full_name }}</a>
                          <p class="tx-12">{{ $lclient->branch ? $lclient->branch->code : null }}{{ $lclient->manual_id_dsp }}</p>
                          </div><!-- media-body -->
                          <a href="{{ route('tenant.client.edit', [$tenant->domain, $lclient->id]) }}"><i class="icon ion-ios-arrow-forward tx-20"></i></a>
                      </div><!-- media -->
                    @endforeach
            
                  </div><!-- media-list -->
          </div><!-- card -->
     </div>
            </div><!-- row -->
    </div>


    

</div>