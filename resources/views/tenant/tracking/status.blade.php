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

    <!--
    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-home tx-gray-300" id="fourth-icon"></i>
                    <h4 class="tx-gray-300" id="fourth-title">{{ strtoupper( __('Branch') ) }}</h4>
                    <p class="tx-gray-300" id="fourth-description">{{ __('Available for delivery') }}</p>
                    <p class="tx-gray-300" id="fourth-localization"></p>
                    <p class="tx-gray-300" id="fourth-date"></p>
                </div>
            </div>
        </div>
    </div>
    -->

    <div class="col-sm-auto col-lg-auto">
        <div class="card card-status">
            <div class="media">
                <div class="text-center">
                    <i class="icon fa fa-check-circle tx-gray-300" id="fifth-icon"></i>
                    <h4 class="tx-gray-300" id="fifth-title">{{ strtoupper( __('Delivered') ) }}</h4>
                    <p class="tx-gray-300" id="fifth-description">{{ __('Client picked up') }}</p>
                    <p class="tx-gray-300" id="fifth-localization">&nbsp;</p>
                    <p class="tx-gray-300" id="fifth-date"></p>
                </div>
            </div>
        </div>
    </div>
</div>