@include("documentos.styles")

<!-- Encabezado -->
<table class="no-border">
    <tr>
        <td class="w100 p-1">
            <img src="assets/images/Logo_ARCOMEX.png" class="" style="width: 120px;">
        </td>
        <td class="w270 p-1 text-left bg-gray1">
            <h3>{{ $datosempresa["nombre"] }}</h3>
            <p class="p-0 m-0 font-10">
                {{ $datosempresa["direccion_1"] }} <br>
                {{ $datosempresa["direccion_2"] }} <br>
                {{ $datosempresa["telefonos"] }} <br>
                {{ $datosempresa["web"] }}
            </p>
        </td>
        <td class="p-1">
            <h1 style="font-size: 18px;">ORDEN DE COMPRA</h1>
            <table>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">No. Orden:</h2>
                    </td>
                    <td class="text-left font-12">{{ $cotizacion_compra["folio"] }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Fecha:</h2>
                    </td>
                    <td class="text-left font-12">{{ substr( $cotizacion_compra["created_at"], 0, 10 ) }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Solicitó:</h2>
                    </td>
                    <td class="text-left font-12">
                        {{ $cotizacion_compra["uregistra"]["clave "] }} {{ $cotizacion_compra["uregistra"]["nombre"] }} <br>
                        {{ $cotizacion_compra["uregistra"]["email"] }} <br>
                        {{ $cotizacion_compra["uregistra"]["telefono"] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!-- end Encabezado -->
<br>
<!-- Tabla proveedor -->
<div class="bg-gray1">
    <div class="w-100 p-1">
        <table class="no-border">
            <tr>
                <td class="text-left w150">
                    <h2 class="font-12">PROVEEDOR:</h2>
                </td>
                <td class="text-left">
                    {{ $cotizacion_compra["proveedor"]["clave"] }} -
                    {{ $cotizacion_compra["proveedor"]["nombre"] }}
                </td>
            </tr>
            <tr>
                <td class="text-left w150">
                    <h2 class="font-12">FECHA DE ENTREGA:</h2>
                </td>
                <td class="text-left">
                </td>
            </tr>
        </table>
    </div>
    <div class="w-100 p-1">
        <table class="no-border">
            <tr>
                <td>
                    <h2 class="font-12 text-left">OBSERVACIONES:</h2>
                    <p class="text-left m-0">
                        {{ $cotizacion_compra["observaciones"] }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<!-- end Tabla proveedor -->
<br>

<!-- Tabla productos -->
<table cellpadding="1" cellspacing="2">
    <tr class="bg-gray1 border-white">
        <td style="font-size: 11px; width:40px">CANT.</td>
        <td style="font-size: 11px; width:50px">UNIDAD</td>
        <td style="font-size: 11px; width:50px">PZAS</td>
        <td style="font-size: 11px">CONCEPTO</td>
        <td style="font-size: 11px; width:50px">COLOR</td>
        <td style="font-size: 11px; width:50px">PESO KG</td>
        <td style="font-size: 11px; width:50px">PRECIO U</td>
        <td style="font-size: 11px; width:50px">TOTAL</td>
        <td style="font-size: 11px; width:90px">PRESUPUESTO</td>
    </tr>
    @foreach($cotizacion_compra["cuerpo"] as $item)
        <tr class="no-border">
            <td style="font-size: 11px"> {{ $item["cantidad"]}} </td>
            <td style="font-size: 11px"> {{ $item["producto"]["unidad"]["abreviatura"] }} </td>
            <td style="font-size: 11px"> {{ $item["piezas"] }}</td>
            <td style="font-size: 11px"> {{ $item["descripcion"]}} </td>
            <td style="font-size: 11px"> {{ $item["color"] }} </td>
            <td style="font-size: 11px"> {{ $item["peso"] }}</td>
            <td style="font-size: 11px"> {{ number_format( $item["precio_u"], 2) }}</td> 
            <td style="font-size: 11px"> {{ number_format( $item["total"], 2) }}</td> 
            <td style="font-size: 11px"> {{ $item["presupuesto"] }} </td>        
        </tr>
    @endforeach
</table>

<br><br>



<!-- Totales -->
<div class="float-right w-50">
    <?php $total =  number_format($cotizacion_compra["total"], 2, '.', '') ?>
    <table class="font-12 border-white">
        <tr> 
            <td class="text-left bg-gray1 w-50">TOTAL</td>
            <td class="text-right bg-ray2 pl-1">
                {{  number_format( $total, 2 ) }} M.N.
            </td>
        </tr>
    </table>
</div>


<b>Adjuntos</b>
<div class="mt-10">
    @foreach ($cotizacion_compra->adjuntosimg as $i => $img)
        <div class="w200 float-left m-3">
            <img src="{{ Storage::disk('public')->url('app/cotizaciones_compra/'.$cotizacion_compra['folio']. '/'.$img->adjunto) }}" alt="" class="img-max">
            {{ $img->descripcion }}
        </div>

        @if ( (($i + 1) % 3) == 0)
            <div class="clear-both h-10"></div>
        @endif
    @endforeach
</div>
