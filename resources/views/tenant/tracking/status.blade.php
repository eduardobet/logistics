<!--
<div class="row justify-content-between">

    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-truck tx-purple"></i>
                    <h4>MIAMI</h4>
                    <p>Recibido en Casillero</p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-plane tx-warning"></i>
                    <h4>PANAMÁ</h4>
                    <p>Centro de Distribucion</p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto ">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-edit tx-primary"></i>
                    <h4>FACTURADO</h4>
                    <p>Enviado a Sucursal</p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-home tx-danger"></i>
                    <h4>SUCURSAL</h4>
                    <p>Disponible para Retiro</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-check-circle tx-success"></i>
                    <h4>ENTREGADO</h4>
                    <p>Retirado por Cliente</p>
                </div>
            </div>
        </div>
    </div>
</div>
-->

<!-- TEXTO INACTIVO -->
<div class="row justify-content-between">

    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-plane tx-gray-300" id="first-icon"></i>
                    <h4 class="tx-gray-300" id="first-title">{{ __('ORIGIN') }}</h4>
                    <p class="tx-gray-300" id="first-description">{{ __('Preparing shipment') }}</p>
                    <p class="tx-gray-300" id="first-localization"></p>
                    <p class="tx-gray-300" id="first-date"></p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-truck tx-gray-300" id="second-icon"></i>
                    <h4 class="tx-gray-300" id="second-title">PANAMÁ</h4>
                    <p class="tx-gray-300" id="second-description">{{ __('Distribution center') }}</p>
                    <p class="tx-gray-300" id="second-localization"></p>
                    <p class="tx-gray-300" id="second-date"></p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto ">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-edit tx-gray-300" id="third-icon"></i>
                    <h4 class="tx-gray-300" id="third-title">{{ strtoupper( __('Invoiced') ) }}</h4>
                    <p class="tx-gray-300" id="third-description">{{ __('Sent to branch') }}</p>
                    <p class="tx-gray-300" id="third-localization"></p>
                    <p class="tx-gray-300" id="third-date"></p>
                </div>
            </div>
        </div>
    </div>


    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-home tx-gray-300" id="fourth"></i>
                    <h4 class="tx-gray-300" id="fourth">{{ strtoupper( __('Branch') ) }}</h4>
                    <p class="tx-gray-300" id="fourth">{{ __('Available for delivery') }}</p>
                    <p class="tx-gray-300" id="fourth-localization"></p>
                    <p class="tx-gray-300" id="fourth-date"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-check-circle tx-gray-300" id="fifth-icon"></i>
                    <h4 class="tx-gray-300" id="fifth-icon">{{ strtoupper( __('Delivered') ) }}</h4>
                    <p class="tx-gray-300" id="fifth-icon">{{ __('Client picked up') }}</p>
                    <p class="tx-gray-300" id="fifth-localization"></p>
                    <p class="tx-gray-300" id="fifth-date"></p>
                </div>
            </div>
        </div>
    </div>
</div>
<!--TEXTO INACTIVO-->