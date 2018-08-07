<div class="row row-xs">
    <div class="col-sm-6 col-lg-4">

        <div class="card card-status">
            <div class="media">
                <i class="icon ion-ios-box-outline tx-purple"></i>
                <div class="media-body">
                    <h1>{{ $tot_warehouses }}</h1>
                    <p>{{ __('Warehouses') }}</p>
                </div><!-- media-body -->
            </div><!-- media -->
        </div><!-- card -->

          </div><!-- col-3 -->

          <div class="col-sm-6 col-lg-4 mg-t-10 mg-sm-t-0">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-person-outline tx-teal"></i>
                <div class="media-body">
                  <h1>{{ $tot_clients }}</h1>
                  <p>{{ __('Clients') }}</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

          <div class="col-sm-6 col-lg-4 mg-t-10 mg-lg-t-0">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-calculator-outline tx-primary"></i>
                <div class="media-body">
                  <h1>{{ $tot_invoices }}</h1>
                  <p>{{ __('Invoices') }}</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

</div><!-- row -->