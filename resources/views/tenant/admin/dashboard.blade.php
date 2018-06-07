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

          <h6 class="slim-pagetitle"> Branch Name </h6>
        </div><!-- slim-pageheader -->

        <div class="row row-xs">
          <div class="col-sm-6 col-lg-4">

            <div class="card card-status">
              <div class="media">
                <i class="icon ion-ios-box-outline tx-purple"></i>
                <div class="media-body">
                  <h1>2123</h1>
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
                  <h1>326</h1>
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
                  <h1>1062</h1>
                  <p>Facturas</p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->

          </div><!-- col-3 -->

        </div><!-- row -->

        <div class="row row-xs mg-t-10">
          <div class="col-lg-8 col-xl-9">
            <div class="row row-xs">
              <div class="col-md-5 col-lg-6 col-xl-5">
                <div class="card card-activities pd-20">
                  <h6 class="slim-card-title">Actividades Recientes</h6>
                  <p>Ultimo Ingreso fue hace 1 Hora</p>

                  <div class="media-list">
                    <div class="media">
                      <div class="activity-icon bg-primary">
                        <i class="icon ion-ios-information-outline"></i>
                      </div><!-- activity-icon -->
                      <div class="media-body">
                        <h6>Nereida a Realizado un Cobro</h6>
                        <p>Realizo un Cobro a PRLA203, Factura LA-2560.</p>
                        <span>2 hours ago</span>
                      </div><!-- media-body -->
                    </div><!-- media -->
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
                    <p class="earning-label">Ganancias de Hoy.</p>
                    <a href="#" class="statement-link">Ver Movimientos <i class="fa fa-angle-right mg-l-5"></i></a>
                  </div>
                </div><!-- dash-headline-item-one -->
              </div><!-- col-7 -->
            </div><!-- row -->
          </div><!-- col-9 -->
          <div class="col-lg-4 col-xl-3 mg-t-10 mg-lg-t-0">
            <div class="card card-people-list pd-20">
              <div class="slim-card-title">Ultimos Clientes</div>
              <div class="media-list">
                <div class="media">
                  <img src="http://via.placeholder.com/500x500" alt="">
                  <div class="media-body">
                    <a href="">Nombre Apellido</a>
                    <p class="tx-12">PRLA000</p>
                  </div><!-- media-body -->
                  <a href=""><i class="icon ion-ios-arrow-forward tx-20"></i></a>
                </div><!-- media -->
                <div class="media">
                  <img src="http://via.placeholder.com/500x500" alt="">
                  <div class="media-body">
                    <a href="">Nombre Apellido</a>
                    <p class="tx-12">PR0000</p>
                  </div><!-- media-body -->
                  <a href=""><i class="icon ion-ios-arrow-forward"></i></a>
                </div><!-- media -->
                <div class="media">
                  <img src="http://via.placeholder.com/500x500" alt="">
                  <div class="media-body">
                    <a href="">Nombre Apellido</a>
                    <p class="tx-12">PR0000</p>
                  </div><!-- media-body -->
                  <a href=""><i class="icon ion-ios-arrow-forward"></i></a>
                </div><!-- media -->
                <div class="media">
                  <img src="http://via.placeholder.com/500x500" alt="">
                  <div class="media-body">
                    <a href="">Nombre Apellido</a>
                    <p class="tx-12">PR0000</p>
                  </div><!-- media-body -->
                  <a href=""><i class="icon ion-ios-arrow-forward"></i></a>
                </div><!-- media -->
                <div class="media">
                  <img src="http://via.placeholder.com/500x500" alt="">
                  <div class="media-body">
                    <a href="">Nombre Apellido</a>
                    <p class="tx-12">PR0000</p>
                  </div><!-- media-body -->
                  <a href=""><i class="icon ion-ios-arrow-forward"></i></a>
                </div><!-- media -->
              </div><!-- media-list -->
            </div><!-- card -->
          </div><!-- col-3 -->
        </div><!-- row -->


      </div><!-- container -->
    </div>
  

    @include('tenant.common._footer')

@endsection
