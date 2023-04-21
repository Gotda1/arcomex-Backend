<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\CategoriaProducto;
use App\Cliente;
use App\Cotizacion;
use App\DatosEmpresa;
use App\ESProducto;
use App\PagoCliente;
use App\PagoProveedor;
use App\Pedido;
use App\PedidoCuerpo;
use App\Producto;
use App\Proveedor;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sabberworm\CSS\Value\Size;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt");
    }

    public function contadores()
    {
        try {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "clientes"         => $this->contarClientes(request("usuarioDB")),
                    "proveedores"      => Proveedor::where("status", 1)->count(),
                    "productos"        => Producto::where("status", 1)->count(),
                    "cotizaciones_pen" => $this->contarCotizacionesPendientes(request("usuarioDB")),
                    "pedidos_pen"      => $this->contarPedidosPendientes(request("usuarioDB")),
                ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Imprime productos y precios de proveedores
     *
     * @return void
     */
    public function catalogoProveedores()
    {
        try {
            $productos = Producto::select("id", "clave", "nombre", "unidad_id")
                ->with("unidad")
                ->with("proveedores:proveedor_id")
                ->orderBy("clave", "asc")
                ->where("status", 1)
                ->get();
            $proveedores = Proveedor::select("id", "clave", "nombre")
                ->where("status", 1)
                ->whereIn("id", function ($query) {
                    $query->select(DB::raw("DISTINCT(proveedor_id)"));
                    $query->from("proveedores_productos");
                })->orderBy("clave", "asc")
                ->get();


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezados
            $sheet->setCellValue('A1', "Clave");
            $sheet->setCellValue('B1', "Producto");

            #   Encabezados nombres proveedores
            foreach ($proveedores as $idx => $proveedor) {
                $celda = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 3);
                $sheet->setCellValue($celda . "1", ($proveedor["clave"] . " " . $proveedor["nombre"]));
            }

            #   Registrto de productos
            foreach ($productos as $idx => $producto) {
                $row = $idx + 2;
                $sheet->setCellValue('A' . $row, $producto["clave"]);
                $sheet->setCellValue('B' . $row, $producto["nombre"]);
                foreach ($producto->proveedores as  $provprod) {
                    #   Encuentra indez del proveedor al que pertenece el producto
                    $indexprov = $proveedores->search(function ($prov) use ($provprod) {
                        return $prov->id == $provprod->proveedor_id;
                    });
                    #   Convierte número de celda a letra
                    $celda = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indexprov + 3);
                    #   Seta valor
                    $sheet->setCellValue($celda . $row, $provprod["pivot"]["precio_lista"]);
                }
            }

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Consulta listado de clientes con su clasificación y últimos dos pedidos realizados
     *
     * @return Response
     */
    public function clientesUltimasCompras()
    {
        try {
            $requester = request("usuarioDB");

            $clientes = Cliente::with("clasificacion:clave,nombre")
                ->where("status", 1);

            $ALLCLT = $this->tienePermiso("ALLCLT");
            if (!$ALLCLT) $clientes->whereIn("usuario_id", [0, $requester["id"]]);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezados
            $sheet->setCellValue('A1', "Clave");
            $sheet->setCellValue('B1', "Cliente");
            $sheet->setCellValue('C1', "Folio pedido 1");
            $sheet->setCellValue('D1', "Fecha pedido 1");
            $sheet->setCellValue('E1', "Importe pedido 1");
            $sheet->setCellValue('F1', "Folio pedido 2");
            $sheet->setCellValue('G1', "Fecha pedido 2");
            $sheet->setCellValue('H1', "Importe pedido 2");

            #   Recorre arreglo de clientes
            foreach ($clientes->get() as $idx => $cliente) {
                $row = ($idx + 2);
                #   Setea clave y nombre del cliente
                $sheet->setCellValue("A" . $row, $cliente["clave"]);
                $sheet->setCellValue("B" . $row, $cliente["nombre"]);

                #   Recorre array de pedidos del cliente
                foreach ($cliente->ultimosPedidos as $idxp => $pedido) {
                    #   Setea folio              
                    $celda = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex((($idxp + 1) * 3) + 0);
                    $sheet->setCellValue($celda . $row, $pedido["folio"]);
                    #   Setea fecha          
                    $celda = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex((($idxp + 1) * 3) + 1);
                    $sheet->setCellValue($celda . $row, $pedido["created_at"]);
                    $total = $pedido["suma"] + ($pedido["iva"] == 1 ? ($pedido["suma"] / 100 * 16) : 0);
                    #   Setea total              
                    $celda = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex((($idxp + 1) * 3) + 2);
                    $sheet->setCellValue($celda . $row, round($total, 2));
                }
            }

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

             # Response
             return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Consulta listado de cotizaciones pendientes
     * con las observaciones de seguimiento
     *
     * @return void
     */
    public function seguimientoCotizaciones()
    {
        try{
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   cotizaciones prospectos
            $cotprospectos = Cotizacion::with("prospecto:id,nombre,clasificacion_clave")
                ->with("usuario:id,nombre")
                ->with("observacionesSeg")
                ->where("catalogo", "prospectos");

            #   Si no tiene permisos para ver todos los prospectos
            #   filtra por clientes asignados
            if (!$this->tienePermiso("ALLPRSP")) {
                $cotprospectos->whereIn("adquisidor_id", function ($q) use ($usuario_id) {
                    $q->select("id");
                    $q->from("prospectos");
                    $q->where("usuario_id", $usuario_id);
                });
            }


            #   cotizaciones clientes
            $cotclientes = Cotizacion::with("cliente:id,clave,nombre,clasificacion_clave")
                ->with("usuario:id,nombre")
                ->with("observacionesSeg.usuario")
                ->where("status", 0)
                ->where("catalogo", "clientes");


            #   Si no tiene permisos para ver todos los clientes
            #   filtra por clientes asignados
            if (!$this->tienePermiso("ALLCLT")) {
                $cotclientes->whereIn("adquisidor_id", function ($q) use ($usuario_id) {
                    $q->select("id");
                    $q->from("clientes");
                    $q->where("usuario_id", $usuario_id);
                });
            }

            $cotizaciones = $cotprospectos->get()->merge($cotclientes->get());

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezados
            $sheet->setCellValue('A1', "Fecha");
            $sheet->setCellValue('B1', "Folio");
            $sheet->setCellValue('C1', "Cliente / Prospecto");
            $sheet->setCellValue('D1', "Catálogo");
            $sheet->setCellValue('E1', "Clasificación");
            $sheet->setCellValue('F1', "Monto");
            $sheet->setCellValue('G1', "Registró");
            
            #   Recorre cotizaciones
            $row = 1;
            foreach ($cotizaciones as $cotizacion) {
                $row ++;
                #   Setea datos de la cotización
                $adquisidor = $cotizacion->catalogo == "clientes" ? $cotizacion->cliente : $cotizacion->prospecto;
                $nombreadq = "-";
                if($adquisidor){
                    $claveadq = $adquisidor && isset($adquisidor["clave"]) ? $adquisidor["clave"] . "-" : "";
                    $nombreadq = $claveadq . $adquisidor["nombre"];
                }
                
                $sheet->setCellValue("A" . $row, $cotizacion["created_at"]);
                $sheet->setCellValue("B" . $row, $cotizacion["folio"]);
                $sheet->setCellValue("C" . $row, $nombreadq);
                $total = $cotizacion["suma"] + ($cotizacion["iva"] == 1 ? ($cotizacion["suma"] / 100 * 16) : 0);
                $sheet->setCellValue("D" . $row, $cotizacion["catalogo"]);
                $sheet->setCellValue("E" . $row, $adquisidor ? $adquisidor->clasificacion->nombre : "-");
                $sheet->setCellValue("F" . $row, round($total, 2));
                $sheet->setCellValue("G" . $row, $cotizacion["usuario"]["nombre"]);

                #   Si hay observaciones, agrega encabezados de la observación
                if(sizeof($cotizacion["observacionesSeg"]) > 0){
                    $row++;
                    $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal('center');
                    $sheet->setCellValue("A" . $row, "Seguimiento");
                    $sheet->mergeCells("A" . $row . ":" . "G" . $row);
                }

                #   Recorre observaciones de la cotización
                foreach ($cotizacion["observacionesSeg"] as $observacion) {
                    $row++;
                    $sheet->setCellValue("A" . $row, $observacion["created_at"]);
                    $sheet->setCellValue("B" . $row, $observacion["observacion"]);
                    $sheet->setCellValue("G" . $row, $observacion["usuario"]["nombre"]);
                    $sheet->mergeCells("B" . $row . ":" . "F" . $row);
                }
            }

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Consulta listado de pedidos entre un rango de fechas
     * @param GET string $finicio  
     * @param GET string $ffin  
     * @return void
     */
    public function pedidos(){
        try{
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            #   pedidos clientes
            $pedidos = Pedido::with("cliente.usuario")
                ->with("usuario:id,clave,nombre")
                ->whereBetween("created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"]);


            #   Si no tiene permisos para ver todos los clientes
            #   filtra por clientes asignados
            if (!$this->tienePermiso("ALLCLT")) {
                $pedidos->whereIn("cliente_id", function ($q) use ($usuario_id) {
                    $q->select("id");
                    $q->from("clientes");
                    $q->where("usuario_id", $usuario_id);
                });
            }

            $pedidos = $pedidos->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezados
            $sheet->setCellValue('A1', "Fecha");
            $sheet->setCellValue('B1', "Vendedor");
            $sheet->setCellValue('C1', "Registró");
            $sheet->setCellValue('D1', "Folio");
            $sheet->setCellValue('E1', "Status");
            $sheet->setCellValue('F1', "Cliente");
            $sheet->setCellValue('G1', "Subtotal");
            $sheet->setCellValue('H1', "Total");
            $sheet->setCellValue('I1', "Monto pagado");
            $sheet->setCellValue('J1', "Monto por cobrar");

            $sumasubtotal = 0;
            $sumatotal = 0;
            $sumapagado = 0;
            $sumapendiente = 0;
            
            #   Recorre cotizaciones
            $row = 1;
            foreach ($pedidos as $pedido) {
                $row ++;
                
                #   Vendedor
                $vendedor = isset($pedido["cliente"]["usuario"]) ? 
                            ( $pedido["cliente"]["usuario"]["clave"] . " - " . $pedido["cliente"]["usuario"]["nombre"] ) : "-";
                #   Registró
                $registro = $pedido["usuario"]["clave"] . " - " . $pedido["usuario"]["nombre"];
                #   Folio
                $folio = $pedido["usuario"]["clave"] . "-" . $pedido["folio"];
                #   Cliente
                $cliente  = isset($pedido["cliente"]) ? ($pedido["cliente"]["clave"] . " - " . $pedido["cliente"]["nombre"]) : "-";
                #   Total
                $total = $pedido["suma"] + ($pedido["iva"] == 1 ? ($pedido["suma"] / 100 * 16) : 0);
                #   Status
                $status = "Pendiente";
                if( $pedido["status"] == -1 ) $status = "Cancelado";
                if( $pedido["status"] == 1 ) $status = "Entregado";
                
                #   Pagado
                $pagado = $pedido->pagado();
                #   Pendiente
                $pendiente = $total - $pagado;

                #   Sumas
                $sumasubtotal  += $pedido["suma"];
                $sumatotal     += $total;
                $sumapagado    += $pagado;
                $sumapendiente += $pendiente;

                #   Setea datos del pedido
                $sheet->setCellValue("A" . $row, $pedido["created_at"]);
                $sheet->setCellValue("B" . $row, $vendedor);
                $sheet->setCellValue("C" . $row, $registro);
                $sheet->setCellValue("D" . $row, $folio);
                $sheet->setCellValue("E" . $row, $status);
                $sheet->setCellValue("F" . $row, $cliente);
                $sheet->setCellValue("G" . $row, $pedido["suma"]);
                $sheet->setCellValue("H" . $row, $total);
                $sheet->setCellValue("I" . $row, $pagado);
                $sheet->setCellValue("J" . $row, $pendiente);
            }


            #   Setea sumas
            $sheet->setCellValue("G" . ( $row + 1 ), $sumasubtotal);
            $sheet->setCellValue("H" . ( $row + 1 ), $sumatotal);
            $sheet->setCellValue("I" . ( $row + 1 ), $sumapagado);
            $sheet->setCellValue("J" . ( $row + 1 ), $sumapendiente);

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Cuenta clientes activos del usuario de acuerdo a sus permisos
     *
     * @param [type] $requester
     * @return void
     */
    private function contarClientes($requester)
    {
        #   permiso para ver todos los clientes
        $ALLCLT = $this->tienePermiso("ALLCLT");

        #   Clientes
        $query = Cliente::with("clasificacion:clave,nombre")
            ->where("status", 1);

        if (!$ALLCLT) $query->whereIn("usuario_id", [0, $requester["id"]]);

        return $query->count();
    }

    /**
     * Cuenta cotizaciones pendientes del usuario de acuerdo a sus permisos
     *
     * @param [type] $requester
     * @return void
     */
    private function contarCotizacionesPendientes($requester)
    {
        #   id requester
        $usuario_id = request("usuarioDB")["id"];
        #   rol requester
        $rol        = request("usuarioDB")["rol"]["clave"];

        #   cotizaciones prospectos
        $cotprospectos = Cotizacion::where("status", 0);
        #   permiso para ver todos los prospectos
        #   Si no tiene permisos para ver todos los prospectos
        #   filtra por prospectos asignados
        $ALLPRSP = $this->tienePermiso($rol, "ALLPRSP");
        if (!$ALLPRSP) {
            $cotprospectos->whereIn("adquisidor_id", function ($q) use ($usuario_id) {
                $q->select("id");
                $q->from("prospectos");
                $q->where("usuario_id", $usuario_id);
            });
        }


        #   cotizaciones clientes
        $cotclientes = Cotizacion::where("status", 0);
        #   permiso para ver todos los clientes
        $ALLCLT = $this->tienePermiso($rol, "ALLCLT");
        #   Si no tiene permisos para ver todos los clientes
        #   filtra por clientes asignados
        if (!$ALLCLT) {
            $cotclientes->whereIn("adquisidor_id", function ($q) use ($usuario_id) {
                $q->select("id");
                $q->from("clientes");
                $q->where("usuario_id", $usuario_id);
            });
        }

        return $cotprospectos->count() + $cotclientes->count();
    }

    /**
     * Cuenta pedidos pendientes del usuario de acuerdo a sus permisos
     *
     * @param [type] $requester
     * @return void
     */
    private function contarPedidosPendientes($requester)
    {
        #   id requester
        $usuario_id = request("usuarioDB")["id"];
        #   rol requester
        $rol        = request("usuarioDB")["rol"]["clave"];
        #   permiso para ver todos los clientes
        $ALLCLT = $this->tienePermiso($rol, "ALLCLT");

        #   cotizaciones
        $query = Pedido::where("status", 0);

        #   Si no tiene permisos para ver todos los clientes
        #   filtra por clientes asignados
        if (!$ALLCLT) {
            $query->whereIn("cliente_id", function ($q) use ($usuario_id) {
                $q->select("id");
                $q->from("clientes");
                $q->where("usuario_id", $usuario_id);
            });
        }

        return $query->count();
    }

    /**
     * Muestra catálogo de almacenes
     *
     * @return void
     */
    public function almacenes(){
        try {
            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "almacenes" => Almacen::where("status", 1)
                                            ->get() 
            ]], 200);            
        } catch (\Throwable $e) {
            report($e);
            
            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Genera reporte de inventario
     *
     * @return void
     */
    public function inventario(){
        try {            
            $privImporte = $this->tienePermiso("DSHBPRC");
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");
            #   almacen
            $almacen_id = request("almacen");

            #   Almacén
            $qAlmacen = Almacen::find( $almacen_id );

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
 
            #   Encabezado 1
            $sheet->setCellValue("A1", "Almacén:  $qAlmacen->id $qAlmacen->nombre $fechainicio - $fechafin");
            $sheet->mergeCells("A1:O1");

            #   Encabezado 2
            $sheet->setCellValue("C2", "En unidades");
            $sheet->mergeCells("C2:H2");

            #   Si tiene permiso para mostrar importes, muestra sus encabezado
            if( $privImporte ){
                $sheet->setCellValue("I2", "En importes");            
                $sheet->mergeCells("I2:O2");
            } 

            #   Encabezado 3
            $sheet->setCellValue("A3", "Clave");
            $sheet->setCellValue("B3", "Producto");
            $sheet->setCellValue("C3", "Inv. Inicial");
            $sheet->setCellValue("D3", "Entradas");
            $sheet->setCellValue("E3", "Salidas");
            $sheet->setCellValue("F3", "Existencia");
            $sheet->setCellValue("G3", "En Pedidos");
            $sheet->setCellValue("H3", "Disponible");

            #   Si tiene permiso para mostrar importes, muestra sus encabezado
            if( $privImporte ){
                $sheet->setCellValue("I3", "Inv. Inicial");
                $sheet->setCellValue("J3", "Entradas");
                $sheet->setCellValue("K3", "Salidas");
                $sheet->setCellValue("L3", "Existencia");
                $sheet->setCellValue("M3", "En Pedidos");
                $sheet->setCellValue("N3", "Disponible");
                $sheet->setCellValue("O3", "Costo");
            }

            #   Estilo
            $sheet->getStyle("A3:O3")->getFont()->setBold(true);

            #   Categorias
            $categorias = CategoriaProducto::where("status", 1)
                                            ->orderBy("orden")
                                            ->get();

            $currRow = 4;            
            $sumTotal = "=";                         
            foreach ($categorias as $categoria) {                
                $sheet->setCellValue("A$currRow", $categoria->nombre);
                $sheet->getStyle("A$currRow")->getFont()->setBold(true);
                $sheet->getStyle("A$currRow")->getAlignment()->setHorizontal('center');
                $sheet->mergeCells("A$currRow:M$currRow");

                $currRow++;

                #   Consulta productos
                $productos = Producto::select("productos.id","unidad_id", "productos.clave", "productos.nombre") 
                    ->where("productos.status",1)
                    ->where("productos.categoria_producto_id", $categoria->id)
                    ->with("unidad")
                    ->with("maxPreciosProv")
                    ->orderBy("productos.nombre", "ASC")
                    ->get();

                $producto_ids = $productos->pluck("id");
        
                #   Consulta movimientos de fecha inicio
                $ultimos_fi = DB::table("es_productos")
                            ->select(DB::raw("max(id) as max"))
                            ->where("almacen_id", $almacen_id)
                            ->where("created_at", "<=", $fechainicio ." 00:00:00")
                            ->whereIn("producto_id", $producto_ids)
                            ->groupBy("producto_id")
                            ->get()
                            ->pluck("max");
        
                #   Consulta movimientos de fecha fin
                $ultimos_ff = DB::table("es_productos")
                            ->select(DB::raw("max(id) as max"))
                            ->where("almacen_id", $almacen_id)
                            ->where("created_at", "<=", $fechafin ." 23:59:59")
                            ->whereIn("producto_id", $producto_ids)
                            ->groupBy("producto_id")
                            ->get()
                            ->pluck("max");
    
                #   Entradas
                $entradas = DB::table("es_productos")
                            ->select("producto_id", DB::raw("sum(cantidad) as total"))
                            ->whereBetween("created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                            ->where("almacen_id", $almacen_id)
                            ->where("tipo", 1)
                            ->whereIn("producto_id", $producto_ids)
                            ->groupBy("producto_id")
                            ->get()
                            ->toArray();  
                
                #   Salidas
                $salidas = DB::table("es_productos")
                            ->select("producto_id", DB::raw("sum(cantidad) as total"))
                            ->whereBetween("created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                            ->where("almacen_id", $almacen_id)
                            ->where("tipo", 0)
                            ->whereIn("producto_id", $producto_ids)
                            ->groupBy("producto_id")
                            ->get()
                            ->toArray();
                            
                #   Consulta productos en pedidos
                $enPedidos = DB::table("pedido_cuerpo AS pc")
                            ->select("pc.producto_id", DB::raw("SUM(pc.cantidad - pc.cantidad_surt) as pedidos"))
                            ->join("pedidos AS pe","pe.id", "=", "pc.pedido_id")
                            ->whereIn("pc.producto_id", $producto_ids)
                            ->where("pe.created_at", "<=", $fechafin)
                            ->where("pe.status", "<>", -1)
                            ->where(function ($q) use($fechafin){
                                $q->where("pe.updated_at", '>', $fechafin)
                                ->orWhere("pe.status", "=", 0);
                            })                            
                            ->groupBy("pc.producto_id")
                            ->get()
                            ->toArray();;              
                
                #   Inventario inicial
                $inv_inicial = ESProducto::whereIn("id", $ultimos_fi)
                                            ->get()
                                            ->toArray();   

                #   Inventario final
                $inv_final   = ESProducto::whereIn("id", $ultimos_ff)
                                            ->get()
                                            ->toArray();                   
                
                $rangoI = $currRow;
                foreach ($productos as $producto) {    
                    $idxInvIni    = array_search($producto->id, array_column( $inv_inicial, "producto_id"));
                    $idxInvFin    = array_search($producto->id, array_column( $inv_final, "producto_id"));
                    $idxEntradas  = array_search($producto->id, array_column( $entradas, "producto_id"));
                    $idxSalidas   = array_search($producto->id, array_column( $salidas, "producto_id"));
                    $idxEnPedidos = array_search($producto->id, array_column( $enPedidos, "producto_id"));
                    $ctoMax       = $producto->maxPreciosProv ? $producto->maxPreciosProv->precio_lista : -1;
                    
                    #   Existencias
                    $totEnPedidos = is_numeric( $idxEnPedidos ) ? $enPedidos[$idxEnPedidos]->pedidos : 0;
                    $invInicial   = is_numeric( $idxInvIni ) ? $inv_inicial[$idxInvIni]["existencias_almacen"] : 0;
                    $invFinal     = is_numeric( $idxInvFin ) ? $inv_final[$idxInvFin]["existencias_almacen"]: 0;
                    $totEntradas  = is_numeric( $idxEntradas ) ? $entradas[$idxEntradas]->total : 0;
                    $totSalidas   = is_numeric( $idxSalidas ) ? $salidas[$idxSalidas]->total : 0;
                    $disponible   = $invFinal - $totEnPedidos;
                   
                    #   Costos
                    $cInvInicial = $ctoMax >= 0 ? ( $invInicial * $ctoMax ) : " - "; 
                    $cInvFinal   = $ctoMax >= 0 ? ( $invFinal * $ctoMax ) : " - ";
                    $cEntradas   = $ctoMax >= 0 ? ( $totEntradas * $ctoMax ) : " - ";
                    $cSalidas    = $ctoMax >= 0 ? ( $totSalidas * $ctoMax ) : " - "; 
                    $cEnPedidos  = $ctoMax >= 0 ? ( $totEnPedidos * $ctoMax ) : " - ";
                    $cDisponible  = $ctoMax >= 0 ? ( $disponible * $ctoMax ) : " - ";
                    
                    $sheet->setCellValue("A" . $currRow, $producto->clave );
                    $sheet->setCellValue("B" . $currRow, $producto->nombre );
                    $sheet->setCellValue("C" . $currRow, $invInicial );
                    $sheet->setCellValue("D" . $currRow, $totEntradas );
                    $sheet->setCellValue("E" . $currRow, $totSalidas );
                    $sheet->setCellValue("F" . $currRow, $invFinal );
                    $sheet->setCellValue("G" . $currRow, $totEnPedidos );
                    $sheet->setCellValue("H" . $currRow, $disponible );
                    if( $privImporte ){
                        $sheet->setCellValue("I" . $currRow, $cInvInicial );
                        $sheet->setCellValue("J" . $currRow, $cEntradas );
                        $sheet->setCellValue("K" . $currRow, $cSalidas );
                        $sheet->setCellValue("L" . $currRow, $cInvFinal );
                        $sheet->setCellValue("M" . $currRow, $cEnPedidos );   
                        $sheet->setCellValue("N" . $currRow, $cDisponible );            
                        $sheet->setCellValue("O" . $currRow, ( $ctoMax >= 0 ? $ctoMax : " - " ));  
                        $sheet->getStyle("I{$currRow}:O{$currRow}")->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    }

                    $currRow++;
                }

                if( $privImporte ){
                    $currRow++;
                    $rangoF = $currRow - 2;
                    $sheet->setCellValue("K" . $currRow, "Existencias" );
                    $sheet->setCellValue("L" . $currRow,  "=SUM(L{$rangoI}:L{$rangoF})");
                    $sumTotal .= "L{$currRow}+";
                    $sheet->getStyle("L{$currRow}")->getNumberFormat()
                           ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    $sheet->getStyle("K{$currRow}")->getFont()->setBold(true);
                    $currRow = $currRow + 2;
                    $rangoI = $currRow;
                }                
            }    

            $currRow = $currRow + 2;
            $sheet->getStyle("K{$currRow}")->getFont()->setBold(true);
            $sheet->setCellValue("K" . $currRow, " TOTAL EXISTENCIA:" );
            $sheet->setCellValue("L" . $currRow,  "{$sumTotal}0");
            $sheet->getStyle("L{$currRow}")->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
   
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Genera reporte de pagos proveedores
     *
     * @return void
     */
    public function pagosProveedores(){
        try {
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            // $fechainicio = "2020-01-01";
            // $fechafin    = "2021-12-31";
        
            #   Consulta proveedores
            $proveedores = Proveedor::where("status", 1)
                            ->get();
    
            #   Consulta movimientos de fecha inicio
            $ultimos_fi = DB::table("pagos_proveedores")
                        ->select(DB::raw("max(id) as max"))
                        ->where("fecha_pago", "<=", $fechainicio ." 00:00:00")
                        ->groupBy("proveedor_id")
                        ->get()
                        ->pluck("max")
                        ->toArray();
            $ultimos_fi = array_chunk($ultimos_fi, 500);
    
            #   Consulta movimientos de fecha fin
            $ultimos_ff = DB::table("pagos_proveedores")
                        ->select(DB::raw("max(id) as max"))
                        ->where("fecha_pago", "<=", $fechafin ." 23:59:59")
                        ->groupBy("proveedor_id")
                        ->get()
                        ->pluck("max")
                        ->toArray();
            $ultimos_ff = array_chunk($ultimos_ff, 500);
            
            #   Cargos
            $qcargos = DB::table("pagos_proveedores")
                        ->select("proveedor_id", DB::raw("sum(importe) as total"))
                        ->whereBetween("fecha_pago", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                        ->where("importe", "<", 0)
                        ->groupBy("proveedor_id")
                        ->get()
                        ->toArray();
                        
            #   Abonos
            $qabonos = DB::table("pagos_proveedores")
                        ->select("proveedor_id", DB::raw("sum(importe) as total"))
                        ->whereBetween("fecha_pago", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                        ->where("importe", ">", 0)
                        ->groupBy("proveedor_id")
                        ->get()
                        ->toArray();
                         
            #   Saldo iniciales
            $saldos_iniciales = [];
            foreach ($ultimos_fi as $array) {
                $pagos = PagoProveedor::whereIn("id", $array)
                    ->get()
                    ->toArray();   
                $saldos_iniciales = array_merge($saldos_iniciales, $pagos);
            }
            
            #   Saldos finales
            $saldos_finales   = [];
            foreach ($ultimos_ff as $array) {
                $pagos = PagoProveedor::whereIn("id", $array)
                    ->get()
                    ->toArray();   

                $saldos_finales = array_merge($saldos_finales, $pagos);
            } 

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezado 1
            $sheet->setCellValue("A1", "RESUMEN DE OPERACIONES PROVEEDORES: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:F1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Clave");
            $sheet->setCellValue("B2", "Proveedor");
            $sheet->setCellValue("C2", "Saldo al {$fechainicio}");
            $sheet->setCellValue("D2", "Pagos");
            $sheet->setCellValue("E2", "Compras");
            $sheet->setCellValue("F2", "Saldo al {$fechafin}");

            $sheet->getStyle("A1:F2")->getFont()->setBold( true );
            $sheet->getStyle("A1:F2")->getFont()->setSize(14);

            $initRow = 3;
            foreach ($proveedores as $proveedor) {
                $idxSaldoIni = array_search($proveedor->id, array_column( $saldos_iniciales, "proveedor_id"));
                $idxSaldoFin = array_search($proveedor->id, array_column( $saldos_finales, "proveedor_id"));
                $idxEntradas = array_search($proveedor->id, array_column( $qcargos, "proveedor_id"));
                $idxSalidas  = array_search($proveedor->id, array_column( $qabonos, "proveedor_id"));
                
                #   Saldos
                $saldoInicial = is_numeric( $idxSaldoIni ) ? $saldos_iniciales[$idxSaldoIni]["saldo"] : 0;
                $saldoFinal   = is_numeric( $idxSaldoFin ) ? $saldos_finales[$idxSaldoFin]["saldo"] : 0;
                $cargos       = is_numeric( $idxEntradas ) ? $qcargos[$idxEntradas]->total  * -1: 0;
                $abonos       = is_numeric( $idxSalidas ) ? $qabonos[$idxSalidas]->total : 0;
                
                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $proveedor->clave );
                $sheet->setCellValue("B" . $initRow, $proveedor->nombre );
                $sheet->setCellValue("C" . $initRow, $saldoInicial );
                $sheet->setCellValue("D" . $initRow, $abonos );
                $sheet->setCellValue("E" . $initRow, $cargos );
                $sheet->setCellValue("F" . $initRow, $saldoFinal );        

                $initRow ++;
            }      

            #   Setea totales
            $final = $initRow - 1;
            $sheet->setCellValue("B" . $initRow, "Total");
            $sheet->setCellValue("C" . $initRow, "=SUM(C3:C{$final})");
            $sheet->setCellValue("D" . $initRow, "=SUM(D3:D{$final})");
            $sheet->setCellValue("E" . $initRow, "=SUM(E3:E{$final})");
            $sheet->setCellValue("F" . $initRow, "=SUM(F3:F{$final})");    

            $sheet->getStyle("C3:F{$initRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_ACCOUNTING_USD);
            $sheet->getStyle("A{$initRow}:F{$initRow}")->getFont()->setBold( true );
            $sheet->getStyle("A{$initRow}:F{$initRow}")->getFont()->setSize(14);

            foreach(range('A','F') as $columnID)
                $sheet->getColumnDimension($columnID)->setAutoSize(true);        

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
   
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Genera reporte de pagos clientes
     *
     * @return void
     */
    public function pagosClientes(){
        try {
            $requester = request("usuarioDB");

            
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            // $fechainicio = "2020-01-01";
            // $fechafin    = "2021-12-31";
        
            #   Consulta clientes
            $clientes = Cliente::where("status", 1);

            $ALLCLT = $this->tienePermiso("ALLCLT");
            if (!$ALLCLT) $clientes->whereIn("usuario_id", [0, $requester["id"]]);
    
            $clientes = $clientes->get();
    
            #   Consulta movimientos de fecha inicio
            $ultimos_fi = DB::table("pagos_clientes")
                        ->select(DB::raw("max(id) as max"))
                        ->where("created_at", "<=", $fechainicio ." 00:00:00")
                        ->groupBy("cliente_id")
                        ->get()
                        ->pluck("max")
                        ->toArray();
            $ultimos_fi = array_chunk($ultimos_fi, 500);

            #   Consulta movimientos de fecha fin
            $ultimos_ff = DB::table("pagos_clientes")
                        ->select(DB::raw("max(id) as max"))
                        ->where("created_at", "<=", $fechafin ." 23:59:59")
                        ->groupBy("cliente_id")
                        ->get()
                        ->pluck("max")
                        ->toArray();
            
            $ultimos_ff = array_chunk($ultimos_ff, 500);

            #   Cargos
            $cargos = DB::table("pagos_clientes")
                        ->select("cliente_id", DB::raw("sum(importe) as total"))
                        ->whereBetween("created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                        ->where("importe","<", 0)
                        ->groupBy("cliente_id")
                        ->get()
                        ->toArray();

            #   Abonos
            $abonos = DB::table("pagos_clientes")
                        ->select("cliente_id", DB::raw("sum(importe) as total"))
                        ->whereBetween("created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                        ->where("importe",">", 0)
                        ->groupBy("cliente_id")
                        ->get()
                        ->toArray();
            
            #   Saldo iniciales
            $saldos_iniciales = [];
            foreach ($ultimos_fi as $array) {
                $pagos = PagoCliente::whereIn("id", $array)
                    ->get()
                    ->toArray();   
                $saldos_iniciales = array_merge($saldos_iniciales, $pagos);
            }
            
            #   Saldos finales
            $saldos_finales = [];
            foreach ($ultimos_ff as $array) {
                $pagos = PagoCliente::whereIn("id", $array)
                    ->get()
                    ->toArray();   

                $saldos_finales = array_merge($saldos_finales, $pagos);
            }          

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezado 1
            $sheet->setCellValue("A1", "RESUMEN DE OPERACIONES CLIENTES: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:F1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Clave");
            $sheet->setCellValue("B2", "Cliente");
            $sheet->setCellValue("C2", "Saldo Inicial");
            $sheet->setCellValue("D2", "Cargos");
            $sheet->setCellValue("E2", "Abonos");
            $sheet->setCellValue("F2", "Saldo final");

            foreach ($clientes as $i => $cliente) {

                $initRow = $i + 3;

                $idxSaldoIni = array_search($cliente->id, array_column( $saldos_iniciales, "cliente_id"));
                $idxSaldoFin = array_search($cliente->id, array_column( $saldos_finales, "cliente_id"));
                $idxEntradas = array_search($cliente->id, array_column( $cargos, "cliente_id"));
                $idxSalidas  = array_search($cliente->id, array_column( $abonos, "cliente_id"));
                
                #   Saldos
                $saldoInicial = is_numeric( $idxSaldoIni ) ? $saldos_iniciales[$idxSaldoIni]["saldo"] : 0;
                $saldoFinal   = is_numeric( $idxSaldoFin ) ? $saldos_finales[$idxSaldoFin]["saldo"] : 0;
                $totCargos    = is_numeric( $idxEntradas ) ? ($cargos[$idxEntradas]->total) * -1 : 0;
                $totAbonos    = is_numeric( $idxSalidas ) ? $abonos[$idxSalidas]->total : 0;


                $clientes[ $i ]["saldoInicial"] = $saldoInicial;
                $clientes[ $i ]["saldoFinal"] = $saldoFinal;
                $clientes[ $i ]["totCargos"] = $totCargos;
                $clientes[ $i ]["totAbonos"] = $totAbonos;
                
                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $cliente->clave );
                $sheet->setCellValue("B" . $initRow, $cliente->nombre );
                $sheet->setCellValue("C" . $initRow, $saldoInicial );
                $sheet->setCellValue("D" . $initRow, $totCargos );
                $sheet->setCellValue("E" . $initRow, $totAbonos );
                $sheet->setCellValue("F" . $initRow, $saldoFinal );        

            }

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
   
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }
    
    public function movimientosClientes(){
        try {
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            #  Pedidos en el rango de fechas
            $pedidos = PagoCliente::select("referencia")
                                ->whereBetween("created_at", [$fechainicio, $fechafin])
                                ->groupBy("referencia")
                                ->get()
                                ->pluck("referencia");
            #   Pagos
            $pagos = PagoCliente::with(["cliente", "usuario"])
                                    ->whereIn("referencia", $pedidos)
                                    ->get();

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezado 1
            $sheet->setCellValue("A1", "REPORTE DETALLE DE PAGOS CLIENTES: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:F1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Fecha");
            $sheet->setCellValue("B2", "Referencia");
            $sheet->setCellValue("C2", "Cliente");
            $sheet->setCellValue("D2", "Importe");
            $sheet->setCellValue("E2", "Observaciones");
            $sheet->setCellValue("F2", "Usuario");

            foreach ($pagos as $i => $pago) {
                $initRow = $i + 3;                
                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $pago->created_at );
                $sheet->setCellValue("B" . $initRow, $pago->referencia );
                $sheet->setCellValue("C" . $initRow, $pago->cliente ? $pago->cliente->nombre : "-" );
                $sheet->setCellValue("D" . $initRow, $pago->importe );
                $sheet->setCellValue("E" . $initRow, $pago->observaciones );
                $sheet->setCellValue("F" . $initRow, $pago->usuario ? $pago->usuario->nombre : "-" );        
            }

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();
   
            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Genera reporte de ventas por producto
     *
     * @return void
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function ventasPorProducto(){
        try {
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            //$fechainicio = "2020-01-01";
            //$fechafin    = "2021-12-31";

            #   Movimientos
            $productos = PedidoCuerpo::select(
                                    DB::raw("SUM(pedido_cuerpo.cantidad) AS cant_vendida"), 
                                    DB::raw("SUM(pedido_cuerpo.precio) AS monto_vendido"), 
                                    "pedido_id", 
                                    "producto_id",
                                    "prod.clave",
                                    "prod.nombre",
                                    "u.abreviatura"
                                )
                                ->join("pedidos AS ped", "ped.id", "=", "pedido_id")
                                ->join("productos AS prod", "prod.id", "=", "producto_id")
                                ->join("unidades AS u", "u.id", "=", "prod.unidad_id")
                                ->whereBetween("ped.created_at", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                                ->where("ped.status", 1)
                                ->groupBy("producto_id")
                                ->orderBy("monto_vendido", "DESC")
                                ->get();
           // return $productos;
            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezado 1
            $sheet->setCellValue("A1", "PRODUCTOS MÁS VENDIDOS: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:E1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Clave");
            $sheet->setCellValue("B2", "Producto");
            $sheet->setCellValue("C2", "Cantidad");
            $sheet->setCellValue("D2", "Unidad");
            $sheet->setCellValue("E2", "Monto");

            foreach ($productos as $i => $item ) {
                $initRow = $i + 3;

                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $item->clave );
                $sheet->setCellValue("B" . $initRow, $item->nombre );
                $sheet->setCellValue("C" . $initRow, round($item->cant_vendida,2));
                $sheet->setCellValue("D" . $initRow, $item->abreviatura );
                $sheet->setCellValue("E" . $initRow, round($item->monto_vendido,2) ); 
            }            

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
   
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Genera reporte de pedidos surtidos
     *
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function reportePedidosSurtidos(){
        try {
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            #   pedidos
            $pedidos = Pedido::select("pedidos.id", "cliente_id", "folio", "created_at", "suma", "total", "iva", "status", DB::raw("sum(cantidad_surt * precio) as surtido_precio"))
                                ->with("cliente:id,clave,nombre")
                                ->join('pedido_cuerpo', 'pedido_cuerpo.pedido_id', '=', 'pedidos.id')
                                ->groupBy('pedidos.id')
                                ->where("cantidad_surt", ">", 0)
                                ->whereBetween("created_at", [$fechainicio, $fechafin]);
            
            #   Si no tiene permiso para ver todos los clientes, 
            #   filtra pedidos por sus clientes asignados
            if(!$this->tienePermiso("ALLCLT")){
                $usuario_id = request("usuarioDB")["id"];
                $clientes   = Cliente::whereIn("usuario_id", [0, $usuario_id])
                                        ->get()
                                        ->pluck("id");
                $pedidos->whereIn("clientes_id", $clientes);
            }

            #   Encabezado 1
            $sheet->setCellValue("A1", "NOTAS SURTIFAS: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:E1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Folio");
            $sheet->setCellValue("B2", "Fecha");
            $sheet->setCellValue("C2", "Cliente");
            $sheet->setCellValue("D2", "Solicitado");
            $sheet->setCellValue("E2", "Surtido");

            foreach ($pedidos->get() as $i => $item ) {
                $initRow = $i + 3;
                $surtido = $item->status == 1 ? $item->total :
                           ($item->surtido_precio * ($item->iva == 1 ? 1 : 1.16));

                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $item->folio );
                $sheet->setCellValue("B" . $initRow, $item->created_at );
                $sheet->setCellValue("C" . $initRow, $item->cliente ? $item->cliente->nombre : "-" );
                $sheet->setCellValue("D" . $initRow, $item->total);
                $sheet->setCellValue("E" . $initRow, round($surtido,2));
            }   
            
            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData) ]
            ], 200);
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
         
    }


    public function prueba()
    {
        $pedido = Pedido::with("cuerpo")->find(1);
        $datosempresa = DatosEmpresa::first();

        // return view('mails.pedidoeliminado')
        // ->with("pedido", $pedido)
        // ->with("datosempresa", $datosempresa);
        $destinatarios = DB::table("usuarios")->whereIn("rol", ["ADMN"])->get()->pluck("email");
        dd($destinatarios);
        //Mail::to($destinatarios)->send(new NuevoPedidoMailer(1));
    }
}
