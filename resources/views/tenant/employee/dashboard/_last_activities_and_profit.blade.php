<div class="row row-xs mg-t-10">

    <div class="col-lg-8 col-xl-9">

        <div class="row row-xs">

            <div class="col-md-5 col-lg-6 col-xl-5">
                <div class="card card-activities pd-20">
                  <h6 class="slim-card-title">{{ __('Recent activities') }}</h6>
                  <p>Ultimo Ingreso fue hace 1 Hora</p>

                  <div class="media-list">
                    
                    
                    @foreach ($branch->notifications as $notif)
                      <div class="media">
                        <div class="activity-icon bg-primary">
                          <i class="icon ion-ios-information-outline"></i>
                        </div><!-- activity-icon -->
                        <div class="media-body">
                          <h6>{{ $notif['data']['title'] }}</h6>
                          <p>{{ $notif['data']['content'] }}</p>
                          <span>{{ $notif['data']['created_at'] }}</span>
                        </div><!-- media-body -->
                      </div><!-- media -->

                    @endforeach

                    <div class="media">
                      <div class="activity-icon bg-success">
                        <i class="icon ion-ios-information-outline"></i>
                      </div><!-- activity-icon -->
                      <div class="media-body">
                        <h6>Franklin Entrego Paquete</h6>
                        <p>Entrego Paquete de PR578, Almacén 26541.</p>
                        <span>2 hours ago</span>
                      </div><!-- media-body -->
                    </div><!-- media -->

                    <div class="media">
                      <div class="activity-icon bg-purple">
                        <i class="icon ion-ios-information-outline"></i>
                      </div><!-- activity-icon -->
                      <div class="media-body">
                        <h6>Franklin a Realizado un Cobro</h6>
                        <p>Realizo un Cobro a PR578, Factura M8-95325.</p>
                        <span>2 hours ago</span>
                      </div><!-- media-body -->
                    </div><!-- media -->

                  </div><!-- media-list -->

                </div><!-- card -->
              </div><!-- col-5 -->

              <div class="col-md-7 col-lg-6 col-xl-7 mg-t-10 mg-md-t-0">
                <div class="dash-headline-item-one">
                  <div id="chartMultiBar1" class="chart-rickshaw"></div>
                  <div class="dash-item-overlay">
                    <h1>$3,350</h1>
                    <p class="earning-label">{{ __('Today profits') }}</p>
                    <a href="#" class="statement-link"> {{ __('Show movements') }} <i class="fa fa-angle-right mg-l-5"></i></a>
                  </div>
                </div><!-- dash-headline-item-one -->
              </div><!-- col-7 -->

            </div><!-- row -->
    </div>


    <div class="col-lg-4 col-xl-3 mg-t-10 mg-lg-t-0">
        <div class="card card-people-list pd-20">
            <div class="slim-card-title">{{ __('Last clients') }}</div>
                <div class="media-list">
                  
                    @foreach ($last_5_clients as $lclient)
                      <div class="media">
                          <img src="https://via.placeholder.com/500x500" alt="">
                          <div class="media-body">
                          <a href="">{{ $lclient->full_name }}</a>
                          <p class="tx-12">{{ $lclient->boxes->first()->branch_code }}{{ $lclient->id }}</p>
                          </div><!-- media-body -->
                          <a href=""><i class="icon ion-ios-arrow-forward tx-20"></i></a>
                      </div><!-- media -->
                    @endforeach
            
                  </div><!-- media-list -->
          </div><!-- card -->
     </div>

</div>