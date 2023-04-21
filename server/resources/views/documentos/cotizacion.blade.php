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
            <h1 style="font-size: 18px;">COTIZACIÓN</h1>
            <table>
                <tr>
                    <td class="text-left"> <h2 class="font-12">Folio:</h2> </td>
                    <td class="text-left font-12">{{ $cotizacion["folio"] }}</td>
                </tr>
                <tr>
                    <td class="text-left"> <h2 class="font-12">Fecha:</h2> </td>
                    <td class="text-left font-12">{{ substr( $cotizacion["created_at"], 0, 10 ) }}</td>
                </tr>
                <tr>
                    <td class="text-left"> <h2 class="font-12">Ejecutivo:</h2> </td>
                    <td class="text-left font-12">
                        {{ $cotizacion["usuario"]["nombre"] }} <br>
                        {{ $cotizacion["usuario"]["email"] }} <br>
                        {{ $cotizacion["usuario"]["telefono"] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!-- end Encabezado -->
<br>
<!-- Tabla cliente -->
<div class="bg-gray1" >
    <div class="w-50 float-left p-1">
        <table class="no-border">
            <tr>
                <td class="text-left w100">
                    <h2 class="font-12">CLIENTE:</h2>    
                </td>
                <td class="text-left">
                    {{ $cotizacion["adquisidor"]["clave"] }} 
                    {{ $cotizacion["adquisidor"]["nombre"] }}
                </td>     
            </tr>
            
            <tr>
                <td class="text-left">
                    <h2 class="font-12">TELÉFONO:</h2></td>
                <td class="text-left">
                    {{ $cotizacion["adquisidor"]["telefono"] }}
                </td>     
            </tr>

            <tr>
                <td class="text-left"><h2 class="font-12">E-MAIL:</h2></td>
                <td class="text-left">{{ $cotizacion["adquisidor"]["email"] }}</h2></td>
            </tr>
        </table>
    </div>
    <div class="w-50 float-left p-1">
        <table class="no-border">
            <tr>
                <td class="text-left w150">
                    <h2 class="font-12">CONDICIÓN DE PAGO:</h2>
                </td>
                <td class="text-left">{{ $cotizacion["forma_pago"] }}</td>
            </tr>
            
            <tr>
                <td class="text-left"><h2 class="font-12">TIEMPO ENTREGA:</h2></td>
                <td class="text-left">{{ $cotizacion["tiempo_entrega"] }}</td>
            </tr>

            <tr>
                <td class="text-left"><h2 class="font-12">LOCALIDAD:</h2></td>
                <td class="text-left">{{ $cotizacion["localidad"] }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<!-- end Tabla cliente -->
<br>

<!-- Tabla productos -->
<table cellpadding="1" cellspacing="2" class="font-12">
    <tr class="bg-gray1 border-white">
        <td  class="font-12">COD.</td>
        <td  class="font-12">CANT.</td>
        <td  class="font-12">UNIDAD</td>
        <td  class="font-12">PZ</td>
        <td  class="font-12" style="width: 170px;">NOMBRE</td>
        <td  class="font-12">PRECIO</td>
        <td  class="font-12">DESC</td>
        <td  class="font-12">P/U</td>
        <td  class="font-12">IMPORTE</td>
    </tr>
    @foreach($cotizacion["cuerpo"] as $item)
        <?php $unidadcve = $item["producto"]["unidad"]["clave"] ?>
        <tr class="no-border" >
            <td  class="font-12"> {{ $item["producto"]["clave"]}} </td>
            <td  class="font-12"> {{ $item["cantidad"]}} </td>
            <td  class="font-12"> {{ $item["producto"]["unidad"]["abreviatura"] }} </td>
            <td  class="font-12"> 
                <!-- Si son unidades regulares muestra piezas -->
                @if( in_array( $unidadcve, ["M", "M2", "L"]))
                    <span> {{ $item["piezas"] }} </span>
                @else
                    <span> - </span>
                @endif
            </td>
            <td  class="font-12"> {{ $item["producto"]["especial"] == 1 ? $item["descripcion"] : $item["producto"]["nombre"]}} </td>
            <td  class="font-12"> {{ number_format( $item["precio_lista"], 2 ) }} </td>
            <td  class="font-12"> {{ $item["descuento"] }} %</td>
            <td  class="font-12"> {{ number_format( $item["precio"], 2 ) }} </td>
            <td  class="font-12"> {{ number_format( ($item["precio"] * $item["cantidad"]), 2 ) }} </td>
        </tr>
    @endforeach
</table>

<br>

<!-- Totales -->
<div class="float-right">
    <?php 
        $iva = ( $cotizacion["total"] -  $cotizacion["suma"] ); 
        $total =  number_format($cotizacion["total"], 2, '.', '');
    ?>
    <table class="font-12 border-white">
        <tr>
            <td rowspan="4" class="text-left no-border pr-1">
            </td>
            <td class="w200 text-left bg-gray1">SUBTOTAL</td>
            <td class="w150 text-right pl-1">
                {{ number_format( $cotizacion["suma"], 2 ) }} M.N.
            </td>
        </tr>
        <tr>
            <td class="text-left bg-gray1">IVA</td>
            <td class="text-right pl-1">
                {{ number_format( $iva, 2 ) }} M.N.
            </td>
        </tr>
            <td class="text-left bg-gray1">TOTAL</td>
            <td class="text-right bg-ray2 pl-1">
                {{  number_format( $total, 2 ) }} M.N.
            </td>
        </tr>
    </table>
</div>

<div class="clear-both"></div>
<br>
<p class="mt-10">
    <b>Observaciones</b> <br>
    {{ $cotizacion["observaciones"] }}
</p>


<b>Adjuntos</b>
<div class="mt-10">
    @foreach ($cotizacion->adjuntosimg as $i => $img)
        <div class="w200 float-left m-3">
            <img src="{{ Storage::disk('public')->url('app/cotizaciones/'.$cotizacion['folio']. '/'.$img->adjunto) }}" alt="" class="img-max">
            {{ $img->descripcion }}
        </div>

        @if ( (($i + 1) % 3) == 0)
            <div class="clear-both h-10"></div>
        @endif
    @endforeach
</div>

<div class="clear-both "></div>
<br><br>

<p>
    <ul>
        <li style="font-size: 12px; font-weight:bold">Esta cotización tiene una vigencia de {{ $cotizacion["vigencia"]}}</li>
        <li style="font-size: 12px;">La piedra es un producto natural por lo cual puede variar en su Tonalidad, Beta y Peso por lo que no se permitirá seleccionarla</li>
        <li style="font-size: 12px;">NO HAY CAMBIOS NI DEVOLUCIONES</li>
        <li style="font-size: 12px;">Todo el material se entregará a pie de camión contemplando una merma del 5%</li>
        <li style="font-size: 12px;">EL TRASLADO DEL MATERIAL CORRE POR CUENTA Y RIESGO DEL CLIENTE SIENDO RESPONSABILIDAD DEL MISMO ASEGURAR SU CARGA.</li>
    </ul>
</p>
