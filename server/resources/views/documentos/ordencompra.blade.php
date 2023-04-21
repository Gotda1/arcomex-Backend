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
            <h1 style="font-size: 18px;">NOTA DE ENTRADA</h1>
            <table>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Folio:</h2>
                    </td>
                    <td class="text-left font-12">{{ $orden_compra["folio"] }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Fecha:</h2>
                    </td>
                    <td class="text-left font-12">{{ substr( $orden_compra["created_at"], 0, 10 ) }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Solicitó:</h2>
                    </td>
                    <td class="text-left font-12">
                        {{ $orden_compra["uregistra"]["clave "] }} {{ $orden_compra["uregistra"]["nombre"] }} <br>
                        {{ $orden_compra["uregistra"]["email"] }} <br>
                        {{ $orden_compra["uregistra"]["telefono"] }}
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
                    {{ $orden_compra["proveedor"]["clave"] }} -
                    {{ $orden_compra["proveedor"]["nombre"] }}
                    {{ $orden_compra["proveedor"]["rfc"] ? " |  RFC." . $orden_compra["proveedor"]["rfc"] : "" }}
                    {{ $orden_compra["proveedor"]["telefono"] ? " | TEL. " . $orden_compra["proveedor"]["telefono"] : ""}}
                </td>
            </tr>
        </table>
    </div>
    <div class="w-100 p-1">
        <table class="no-border">
            @if( $orden_compra["en_almacen"] == 0)
                <tr>
                    <td class="w150 text-left">
                        <h2 class="font-12">DIRECCIÓN</h2>
                    </td>
                    <td class="text-left">
                        {{ $orden_compra["direccion"]["calle"] }}
                        {{ $orden_compra["direccion"]["numero"] }}
                        {{ $orden_compra["direccion"]["colonia"] }}
                        {{ $orden_compra["direccion"]["cp"] }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <h2 class="font-12 text-left">REFERENCIA</h2>
                    </td>
                    <td class="text-left">
                        {{ $orden_compra["direccion"]["referencia"] }}
                        {{ $orden_compra["direccion"]["tipo_obra"] }}
                    </td>
                </tr>
            @else
                <tr>
                    <td class="w150 text-left">
                        <h2 class="font-12">DIRECCIÓN</h2>
                    </td>
                    <td class="text-left">Av. Camino Real a Colima 381, Haciendas La Candelaria, 45645 Jal.</td>
                </tr>                        
            @endif

            @if( $orden_compra["en_almacen"] == 0)
            <tr>
                <td class="text-left w150">
                    <h2 class="font-12">RECIBE:</h2>
                </td>
                <td class="text-left">
                    {{ $orden_compra["direccion"]["nombre_recibe"] }} |
                    {{ $orden_compra["direccion"]["telefono"] }}
                </td>
            </tr>
            @endif
            <tr>
                <td>
                    <h2 class="font-12 text-left">OBSERVACIONES</h2>
                </td>
                <td class="text-left">
                    {{ $orden_compra["observaciones"] }}
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<!-- end Tabla proveedor -->
<br>

<!-- Tabla productos -->
<table cellpadding="1" cellspacing="2" class="font-12">
    <tr class="bg-gray1 border-white">
        <td>COD.</td>
        <td>CANT.</td>
        <td>UNIDAD</td>
        <td>PZ</td>
        <td>NOMBRE</td>
        <td>P/U</td>
        <td>IMPORTE</td>
    </tr>
    @foreach($orden_compra["cuerpo"] as $item)
        <?php $unidadcve = $item["producto"]["unidad"]["clave"] ?>
        <tr class="no-border">
            <td> {{ $item["producto"]["clave"]}} </td>
            <td> {{ $item["cantidad"]}} </td>
            <td> {{ $item["producto"]["unidad"]["abreviatura"] }} </td>
            <td> 
                <!-- Si son unidades regulares muestra piezas -->
                @if( in_array( $unidadcve, ["M", "M2", "L"]))
                    <span> {{ $item["piezas"] }} </span>
                @else
                    <span> - </span>
                @endif
            <td>
                {{ $item["descripcion"]}} 
            </td>
            @if($PRCOCMP)
                <td> {{ number_format( $item["precio"] ) }} </td>
            @else
                <td> - </td>
            @endif

            @if($PRCOCMP)
                <td> {{ number_format( $item["precio"] * $item["cantidad"], 2) }} </td>
            @else
                <td> - </td>
            @endif
        </tr>
    @endforeach
</table>

<br>

<!-- Totales -->
@if($PRCOCMP)
<div class="float-right">
    <?php
        $formatterES = new NumberFormatter("es_MX", NumberFormatter::SPELLOUT); 
        $iva = $orden_compra["iva"];
        $total =  number_format($orden_compra["total"], 2, '.', '');
        $array_total = explode( ".", $total );
    ?>
    <table class="font-12 border-white">
        <tr>
            <td rowspan="3" class="text-left no-border pr-1" style="text-transform: uppercase;">
                {{ $formatterES->format($array_total[0]) }} PESOS
                {{ $array_total[1]}}/100 MN
            </td>
            <td class="w200 text-left bg-gray1">SUBTOTAL</td>
            <td class="w150 text-right pl-1">
                {{ number_format( $orden_compra["subtotal"], 2 ) }} M.N.
            </td>
        </tr>
        <tr>
            <td class="text-left bg-gray1">IVA TRASLADADO 16%</td>
            <td class="text-right pl-1">
                {{ number_format( $iva, 2 ) }} M.N.
            </td>
        </tr>
        <td class="text-left bg-gray1">TOTAL</td>
        <td class="text-right bg-ray2 pl-1">
            {{ number_format( $total, 2 ) }} M.N.
        </td>
        </tr>
    </table>
</div>
@endif