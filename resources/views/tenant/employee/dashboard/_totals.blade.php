<div class="row row-xs">
    <div class="col-sm-6 col-lg-3">

        <div class="card card-status">
            <div class="media">
                <i class="icon ion-ios-box-outline tx-purple"></i>
                <div class="media-body">
                    <h3>{{ $tot_warehouses }}</h3>
                    <p>{{ __('Warehouses') }}</p>
                </div><!-- media-body -->
            </div><!-- media -->
        </div><!-- card -->

          </div><!-- col-3 -->

          <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-person-outline tx-teal"></i>
                <div class="media-body">
                  <h3>{{ $tot_clients }}</h3>
                  <p>{{ __('Clients') }}</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

          <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-calculator-outline tx-primary"></i>
                <div class="media-body">
                  <h3>{{ $tot_invoices }}</h3>
                  <p>{{ __('Invoices') }}</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

          <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-calculator-outline tx-primary"></i>
                <div class="media-body">
                  <h3>
                    @can('show-invoice')
                    <a href="{{ route('tenant.outstandings.list', [$tenant->domain, 'branch_id' => $branch->id,]) }}">
                      {{ number_format($outstanding_invoices, 2) }}
                    </a>
                    @endcan
                  </h3>
                  <p>{{ __('Outstandings') }}</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

</div><!-- row -->