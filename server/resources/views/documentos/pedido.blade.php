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
            <h1 style="font-size: 18px;">
                PEDIDO
                @if( $pdf == "PDFCMP" )
                    COMPRAS                    
                @elseif( $pdf == "PDFCLTE" ) 
                    CLIENTE
                @elseif( $pdf == "PDFALM" ) 
                    ALMACÉN
                @endif
            </h1>
            <table>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Folio:</h2>
                    </td>
                    <td class="text-left font-12">{{ $pedido["folio"] }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Fecha:</h2>
                    </td>
                    <td class="text-left font-12">{{ substr( $pedido["created_at"], 0, 10 ) }}</td>
                </tr>
                <tr>
                    <td class="text-left">
                        <h2 class="font-12">Ejecutivo:</h2>
                    </td>
                    <td class="text-left font-12">
                        {{ $pedido["usuario"]["nombre"] }} <br>
                        {{ $pedido["usuario"]["email"] }} <br>
                        {{ $pedido["usuario"]["telefono"] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!-- end Encabezado -->
<br>
<!-- Tabla cliente -->
<div class="bg-gray1">
    <div class="w-100 p-1">
        <table class="no-border">
            <tr>
                <td class="text-left w150">
                    <h2 class="font-12">CLIENTE:</h2>
                </td>
                <td class="text-left">
                    {{ $pedido["cliente"]["clave"] }} -
                    {{ $pedido["cliente"]["nombre"] }}
                    {{ $pedido["cliente"]["rfc"] ? "  RFC." . $pedido["cliente"]["rfc"] : "" }}
                    {{ $pedido["cliente"]["telefono"] ? "  TEL. " . $pedido["cliente"]["telefono"] : ""}}
                </td>
            </tr>
            <tr>
                <td class="w150 text-left">
                    <h2 class="font-12">FECHA ESTIMADA</h2>
                </td>
                <td class="text-left">
                    {{ $pedido["direccion"]["fecha_estimada"] }}
                </td>
            </tr>
            @if( $pedido["direccion"]["recoge_almacen"] == 0)
            <tr>
                <td class="text-left">
                    <h2 class="font-12">DIRECCIÓN</h2>
                </td>
                <td class="text-left">
                    {{ $pedido["direccion"]["calle"] }}
                    {{ $pedido["direccion"]["numero"] }}
                    {{ $pedido["direccion"]["colonia"] }}
                    {{ $pedido["direccion"]["localidad"] }}
                    {{ $pedido["direccion"]["cp"] }}
                </td>
            </tr>
                @if($pedido["direccion"]["referencia"] || $pedido["direccion"]["referencia"])
                <tr>
                    <td>
                        <h2 class="font-12 text-left">REFERENCIA</h2>
                    </td>
                    <td class="text-left">
                        {{ $pedido["direccion"]["referencia"] }}
                        {{ $pedido["direccion"]["tipo_obra"] }}
                    </td>
                </tr>
                @endif            
            @else
                <tr>
                    <td class="text-left" colspan="2">
                        <h2 class="font-12">PEDIDO PARA RECOGER EN ALMACÉN</h2>
                    </td>
                </tr>
            @endif
            <tr>
                <td class="text-left">
                    <h2 class="font-12">
                        {{ ($pedido["direccion"]["recoge_almacen"] == 1)  ? "RECOGE:"  : "RECIBE:"}}
                    </h2>
                </td>
                <td class="text-left">
                    {{ $pedido["direccion"]["nombre_recibe"] }} |
                    {{ $pedido["direccion"]["telefono"] }}
                </td>
            </tr>
            @if($pedido["observaciones"])
            <tr>
                <td>
                    <h2 class="font-12 text-left">OBSERVACIONES</h2>
                </td>
                <td class="text-left">
                    {{ $pedido["observaciones"] }}
                </td>
            </tr>
            @endif
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
    @foreach($pedido["cuerpo"] as $item)
        <tr class="no-border" >
            <td  class="font-12"> {{ $item["producto"] ? $item["producto"]["clave"] : ""}} </td>
            <td  class="font-12"> {{ $item["cantidad"]}} </td>
            <td  class="font-12"> {{ $item["producto"] ? $item["producto"]["unidad"]["abreviatura"] : "" }} </td>
            <td  class="font-12"> 
                <?php $unidadcve = $item["producto"] ? $item["producto"]["unidad"]["clave"] : "" ?>
                <!-- Si son unidades regulares muestra piezas -->
                @if( in_array( $unidadcve, ["M", "M2", "L"]))
                    <span> {{ $item["piezas"] }} </span>
                @else
                    <span> - </span>
                @endif
            </td>
            <td  class="font-12"> {{  !$item["producto"] || $item["producto"]["especial"] == 1 ? $item["descripcion"] : $item["producto"]["nombre"]}} </td>
            <td  class="font-12"> {{ in_array($pdf, ["PDFCMP", "PDFCLTE"]) ? number_format( $item["precio_lista"], 2 ) : " - " }} </td>
            <td  class="font-12"> {{ in_array($pdf, ["PDFCMP", "PDFCLTE"]) ? ($item["descuento"] . "%") : " - "  }}</td>
            <td  class="font-12"> {{ in_array($pdf, ["PDFCMP", "PDFCLTE"]) ? number_format( $item["precio"], 2 ) : " - "}} </td>
            <td  class="font-12"> {{ in_array($pdf, ["PDFCMP", "PDFCLTE"]) ? number_format( ($item["precio"] * $item["cantidad"]), 2 ) : " - "}} </td>
        </tr>
    @endforeach
</table>


<br>
<hr>
<div class="mt-10">
    @foreach ($pedido->adjuntosimg as $i => $img)
        <div class="w200 float-left m-3">
            <img src="{{ Storage::disk('public')->url('app/pedidos/'.$pedido['folio']. '/'.$img->adjunto) }}" alt="" class="img-max">
            <small>{{ $img->descripcion }}
        </div>
        
        @if ( (($i + 1) % 3) == 0)
        <div class="clear-both h-10"></div>
        @endif
    @endforeach
    <div class="clear-both mt-10"></div>
</div>
<br><br>

<!-- Observaciones internas -->
@if(in_array($pdf, ["PDFCMP", "PDFALM"]))
<h6>Observaciones para producción</h6>
<p>{{ $pedido->observaciones_internas }}</p>
@endif
<hr>

<br>

<!-- Totales -->
<div class="float-right">
    <?php
        $iva = ( $pedido["total"] -  $pedido["suma"] ); 
        $total =  number_format($pedido["total"], 2, '.', '');
        $pagado = $pedido->pagos->sum("importe");
        $porcobrar = $total - $pagado;
    ?>
    
    @if(in_array($pdf, ["PDFCMP", "PDFCLTE"]))
        <table class="font-12 border-white">
            <tr>
                <td rowspan="3" class="text-left no-border pr-1">
                    &nbsp;
                </td>
                <td class="w200 text-left bg-gray1">SUBTOTAL</td>
                <td class="w150 text-right pl-1">
                    {{ number_format( $pedido["suma"], 2 ) }} M.N.
                </td>
            </tr>
            <tr>
                <td class="text-left bg-gray1">IVA</td>
                <td class="text-right pl-1">
                    {{ number_format( $iva, 2 ) }} M.N.
                </td>
            </tr>
            <tr>
                <td class="text-left bg-gray1">TOTAL</td>
                <td class="text-right bg-ray2 pl-1">
                    {{ number_format( $total, 2 ) }} M.N.
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    @if( $pagado >= $total)
                    <h1 style="font-size: 20px; margin-top: 10px">PAGADO</h1>
                    @else
                    <h1 style="font-size: 20px;">SALDO PENDIENTE</h1>
                    @endif
                    <br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="w200 text-left bg-gray1">ANTICIPO</td>
                <td class="w150 text-right pl-1">{{ number_format( $pagado, 2 ) }} M.N.</td>
            </tr>
            <tr>
                <td></td>
                <td class="w200 text-left bg-gray1">POR COBRAR</td>
                <td class="w150 text-right pl-1">{{ number_format( $porcobrar, 2 ) }} M.N.</td>
            </tr>
        </table>
    @endif
</div>

<div class="clear-both "></div>
<br><br>

<p>
    <ul>
        <li style="font-size: 12px;">La piedra es un producto natural por lo cual puede variar en su Tonalidad, Beta y Peso por lo que no se permitirá seleccionarla</li>
        <li style="font-size: 12px;">NO HAY CAMBIOS NI DEVOLUCIONES</li>
        <li style="font-size: 12px;">Todo el material se entregará a pie de camión contemplando una merma del 5%</li>
        <li style="font-size: 12px;">EL TRASLADO DEL MATERIAL CORRE POR CUENTA Y RIESGO DEL CLIENTE SIENDO RESPONSABILIDAD DEL MISMO ASEGURAR SU CARGA.</li>
    </ul>
</p>

@if ($pdf == "PDFALM")
    <table style="width: 453px; height:226px; color:crimson; border: solid 1px crimson; text-align:center; padding:5px; font-size:24px">
        <tr>
            <td style="border: none">
                <strong style="font-size:24px">CANTERAS ARCOMEX GDL</strong> <br>
                ENTREGA DE MATERIAL <br>
                <div style="text-align: left;font-size:24px">
                    Fecha: _________________________ <br>
                    Hora: __________________________ <br>
                    Nombre:  _______________________ <br>
                    Firma: _________________________ <br>
                </div>
            </td>
        </tr>
    </table>
@endif