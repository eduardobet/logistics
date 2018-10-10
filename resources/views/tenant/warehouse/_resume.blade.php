<div class="row col-12">
    <div class="col-2 text-center">Cantidad</div>
    <div class="col-2 text-center">Peso Bruto</div>
    <div class="col-2 text-center">Volume</div>
    <div class="col-2 text-center">Peso Taseable</div>
    <div class="col-2 text-center">En Almacen</div>
    <div class="col-2 text-center">Estado</div>
</div>

<div class="row col-12">
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">{{ $warehouse->qty }} Piezas</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">{{ $invoice->real_weight }} LBS</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">0 Cubic Feet</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">00,0 LBS</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">1 Pieces</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">Recibido</H4></div>
</div>

<div class="row col-12">
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse"></H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">{{ number_format(($invoice->real_weight * 0.4535), 2) }} KGS</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">0.00 m3</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">0.0 KGS</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse">0.00$</H4></div>
    <div class="col-2 text-center"><h4 class="tx-bold tx-inverse"></H4></div>
</div>

</div>
<hr>