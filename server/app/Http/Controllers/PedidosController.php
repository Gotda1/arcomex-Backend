<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\Cliente;
use App\CotizacionAdjunto;
use App\DatosEmpresa;
use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\FormasPago;
use App\Http\Requests\GuardarPedidoRequest;
use App\Mail\CancelaPedidoMailer;
use App\Mail\EliminaPedidoMailer;
use App\Mail\NuevoPedidoMailer;
use App\PagoCliente;
use App\Pedido;
use App\PedidoAdjunto;
use App\PedidoCuerpo;
use App\PedidoDireccion;
use App\PedidoPago;
use App\Producto;
use App\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PedidosController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt")->except(["show"]);
    }
    
    /**
     * Muestra catálogo de vendedores
     *
     * @return void
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function showVendedores(){
        try {
            #   Vendedores
            $vendedores = Usuario::where("rol", "VTAS")
                        ->orderBy("nombre")
                        ->where("status", 1)
                        ->get();
            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "vendedores"  => $vendedores,
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   permiso para ver todos los clientes
            $ALLCLT = $this->tienePermiso( "ALLCLT" );

            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");
            #   Filtro por vendedor
            $vendedor    = request("vendedor");


            #   pedidos
            $query = Pedido::select("pedidos.id", "cliente_id", "folio", "created_at", "suma", "total", "usuario_registra", "iva", "status", DB::raw("sum(cantidad_surt) as surtido"))
                                ->with("cliente:id,clave,nombre")
                                ->with("usuario:id,clave,nombre")
                                ->with("pagos")
                                ->join('pedido_cuerpo', 'pedido_cuerpo.pedido_id', '=', 'pedidos.id')
                                ->groupBy('pedidos.id')
                                ->whereBetween("created_at", [$fechainicio, $fechafin]);

            if( $vendedor ){
                $query->where("usuario_registra", $vendedor);
            }

            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "pedidos"    => $query->get()
            ]], 200);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   permiso para ver todos los clientes
            $ALLCLT = $this->tienePermiso( "ALLCLT" );

            #   Clientes
            $clientes = Cliente::select("id", "clave", "clasificacion_clave", "nombre")
                                ->with("clasificacion:clave,nombre")
                                ->where("status", 1)
                                ->orderBy("nombre", "asc");            
            if(!$ALLCLT)
                $clientes->whereIn("usuario_id", [0, $usuario_id]);
            
            #   Productos
            $productos = Producto::with("unidad")
                                    ->with("enPedidos")
                                    ->where("status", 1)
                                    ->orderBy("nombre", "asc")
                                    ->get();

            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "clientes"  => $clientes->get(),
                    "productos" => $productos,
                    "formas_pago" => FormasPago::all()
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GuardarPedidoRequest $request)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"]; 

            #   Consulta cliente
            $cliente = Cliente::find( $request->cliente_id );
            
            #   Guarda encabezado
            $data = $request->validated();
            $creado = Pedido::create([
                "cliente_id"             => $data["cliente_id"],
                "vendedor_id"            => $cliente->usuario_id,
                "folio"                  => $this->nuevoFolio( $data["cliente_id"] ),
                "observaciones"          => $data["observaciones"],
                "iva"                    => $data["iva"] === true ? 1 : 0,
                "suma"                   => 0,
                "total"                  => 0,
                "observaciones_internas" => $data["observaciones_internas"],
                "status"                 => 0,
                "usuario_registra"       => $usuario_id
            ]);

            #   Guarda cuerpo
            $suma = 0;
            foreach ($data["cuerpo"] as $i => $producto) {
                PedidoCuerpo::create([
                    "pedido_id"    => $creado->id,
                    "producto_id"  => $producto["producto"]["id"],
                    "cantidad"     => $producto["cantidad"],                    
                    "descripcion"  => $producto["descripcion"],
                    "piezas"       => $producto["piezas"],
                    "precio"       => $producto["precio_uni"],
                    "descuento"    => $producto["descuento"] ? $producto["descuento"] : 0,
                    "precio_lista" => $producto["precio_lista"],
                    "orden"        => $i
                ]);
                $suma += ($producto["precio_uni"] * $producto["cantidad"]);
            }

            #   Actualiza pedido con la suma y el total
            $suma = round($suma, 2);
            $total = $creado->iva == 1 ? $this->sumaIVA( $suma ) : $suma;
            Pedido::where( "id", $creado->id )->update( [
                "suma"  => $suma,
                "total" => $total
            ]);

            # Guarda imágenes
            foreach (request("adjuntos") as $imagen) {
                PedidoAdjunto::create([
                    "pedido_id" => $creado->id,
                    "adjunto"       => $this->almacenaImagenB64( "pedidos", $creado->folio, $imagen["source"] ),
                    "descripcion"   => $imagen["descripcion"]
                ]);
            }

            #   Guarda dirección
            $dataInsertDir = [
                "pedido_id"      => $creado->id, 
                "nombre_recibe"  => $data["nombre_recibe"],
                "telefono"       => $data["telefono"],
                "fecha_estimada" => $data["fecha_estimada"],
                "recoge_almacen" => $data["recoge_almacen"] == true ? 1 : 0
            ];

            #   Si no se escoge recoger en almacén, 
            #   agrega los datos de dirección
            if (request("recoge_almacen") == false){
               $dataInsertDir =  array_merge($dataInsertDir, [
                "calle"          => $data["calle"],
                "numero"         => $data["numero"],
                "colonia"        => $data["colonia"],
                "localidad"      => $data["localidad"],
                "cp"             => $data["cp"],
                "referencia"     => $data["referencia"],
                "tipo_obra"      => $data["tipo_obra"],
               ]);
            }

            #   Inserta dirección
            PedidoDireccion::create($dataInsertDir);

            #   Guardar Pago
            $pago = PedidoPago::create([
                "pedido_id"        => $creado->id,
                "forma_pago"       => $data["forma_pago"],
                "importe"          => $data["importe_recibido"],
                "usuario_registra" => $usuario_id
            ]);

            
            #   Inserta cargo
            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldo( $creado->cliente_id );
            PagoCliente::create([
                "cliente_id"       => $creado->cliente_id,
                "pedido_id"        => $creado->id,
                "pedido_pago_id"   => 0,
                "referencia"       => $creado->folio,
                "observaciones"    => "CARGO",
                "importe"          => 0 - $total,
                "saldo"            => ( $saldo + $total ),
                "usuario_registra" => $usuario_id
            ]);

            #   Inserta abono
            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldo( $creado->cliente_id );
            PagoCliente::create([
                "cliente_id"       => $creado->cliente_id,
                "pedido_id"        => $creado->id,
                "pedido_pago_id"   => $pago->id,
                "referencia"       => $creado->folio,
                "observaciones"    => "PAGO INICIAL",
                "importe"          => $data["importe_recibido"],
                "saldo"            => $saldo - $data["importe_recibido"],
                "usuario_registra" => $creado->usuario_registra
           ]);
            
            #   Actualiza saldo pendiente del cliente
            Cliente::where("id", $creado->cliente_id)->update([
                "saldo_pendiente" => ( $saldo - $data["importe_recibido"] )
            ]);

            #   Notifica a almacén
            $destinatarios = DB::table("usuarios")->whereIn("rol", ["ALMC", "COMP"])->get()->pluck("email");
            Mail::to($destinatarios)->send(new NuevoPedidoMailer($creado->id));

            # Response
            return response()->json([
               "head" => "success",
                "body" => [ "pedido" => $creado ]
            ], 200);

        } catch (\Throwable $e) {
            report($e);
            
            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = [ 
                "datosempresa" => DatosEmpresa::first(),
                "pedido"       => Pedido::with("cuerpo.producto.unidad")
                                    ->with("cliente")
                                    ->with("usuario")
                                    ->with("direccion")
                                    ->with("pagos")
                                    ->with("adjuntosimg")
                                    ->find($id),
                "pdf"          => request("pdf"), 
            ];
            $pdf = app('dompdf.wrapper');
            $pdf->loadView("documentos.pedido", $data )
                            ->setPaper('letter');
            return $pdf->stream('pedido.pdf');
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   Consulta pedido a cancelar
            $pedido = Pedido::find( $id );

            #   Cancelar pagos pedido
            $saldo_actual = $this->obtenerSaldo($pedido->cliente_id);
            $restante = $pedido->total - $pedido->pagado();
            $saldo = $saldo_actual - $restante;
            #   Inserta pago cancelación
            PagoCliente::create([
                "cliente_id"       => $pedido->cliente_id,
                "referencia"       => $pedido->folio,
                "pedido_pago_id"   => 0,
                "observaciones"    => "PEDIDO CANCELADO",
                "importe"          => $restante,
                "saldo"            => $saldo,
                "usuario_registra" => $usuario_id
            ]);
            #   Actualiza saldo del cliente
            Cliente::where("id", $pedido->cliente_id)->update([
                "saldo_pendiente" => $saldo
            ]);

            #   Actualiza pedido a status -1 cancelado
            Pedido::where("id", $id)->update([ 
                "status" => -1,
            ]);
            
            #   Notifica a almacén
            $destinatarios = DB::table("usuarios")->whereIn("rol", ["ALMC", "COMP"])->get()->pluck("email");
            Mail::to($destinatarios)->send(new CancelaPedidoMailer($id));
       
            # Response
            return response()->json([
               "head" => "success",
                "body" => [ "pedido" =>  $pedido ]
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   Consulta pedido a borrar
            $pedido = Pedido::find($id); 

            #   Si el pedido ya ha sido surtido, envia alerta
            if($pedido->surtiendo() > 0 && $pedido->status != 1){
                # Response
                return response()->json([
                    "head" => "error",
                    "body" => ["message" => "No se puede cancelar/eliminar un pedido surtido"]
                ], 400);
            }

            #   Saldo 
            $saldo_actual = $this->obtenerSaldo( $pedido->cliente_id );
            #   Restante 
            $restante = $pedido->total - $pedido->pagado();
            #   Si el pedido está cancelado, el importe restante es 0
            $restante = $pedido->status != 0 ? 0 : $restante;

             #   Elimina imagenes del pedido
             $path = "pedidos/" . $pedido->folio . "/";
             if (Storage::exists( $path )) 
                 Storage::deleteDirectory($path);

            #   Elimina pedido y sus tablas hijas 
            Pedido::destroy($id);
            DB::table("pedido_cuerpo")->where("pedido_id", $id)->delete();
            DB::table("pedido_direccion")->where("pedido_id", $id)->delete();
            DB::table("pedido_pagos")->where("pedido_id", $id)->delete();
            DB::table("pedido_adjuntos")->where("pedido_id", $id)->delete();
        

            PagoCliente::create([
                "cliente_id"       => $pedido->cliente_id,
                "pedido_id"        => $pedido->id,
                "pedido_pago_id"   => 0,
                "referencia"       => $pedido->folio,
                "observaciones"    => "PEDIDO ELIMINADO",
                "importe"          => $restante,
                "saldo"            => $saldo_actual - $restante,
                "usuario_registra" => $usuario_id
            ]);

            #   Actualiza saldo pendiente del cliente
            Cliente::where("id", $pedido->cliente_id)->update([
                "saldo_pendiente" => ( $saldo_actual - $restante )
            ]);
           
            #   Notifica a almacén
            $destinatarios = DB::table("usuarios")->whereIn("rol", ["ALMC", "COMP"])->get()->pluck("email");
            Mail::to($destinatarios)->send(new EliminaPedidoMailer($pedido->folio));

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["pedido" => $pedido]
            ], 200, []);
        } catch (\Throwable $e) {
            report($e);
            
            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400, []);
        }
    }

    /**
     * Muestra datos del pedido a surtir
     *
     * @param [type] $id
     * @return void
     */
    public function mostrarPedidoSurtir($id){
        try {
             #   Response
             return response()->json([
                "head" => "success",
                "body" => [ 
                    "almacenes"    => Almacen::where("status", 1) 
                                            ->get(),
                    "pedido"       => Pedido::with("cuerpo.producto.unidad")
                                        ->find($id),
                ]
            ], 200, []);
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
     * Surtir pedido
     *
     * @return void
     */
    public function surtirPedido( Request $request, $id){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   Consulta orden de compra
            $pedido = Pedido::find($id);
            #   Bandera para saber si el pedido fué totalmente surtido
            $surtido = true;

            #   Itera registros
            foreach ($request->egresos as $egresos) {
                foreach ($egresos["egresos"] as $egresos_alm) {
                    #   Producto de la bd con existencias en almacén
                    $productoexs = Producto::with(["existenciasAlmacen"  => function ($query) use($egresos_alm) {
                        $query->where("almacen_id", $egresos_alm["almacen"]["id"]);
                    }])->find( $egresos_alm["producto"]["producto"]["id"] );

                    #   Si el producto es especial, calcula las piezas 
                    #   dividiendo las piezas solicitadas entre las cantidad
                    if( $egresos["producto"]["especial"] == true ){
                        $cantcalc = $egresos["piezas"] / $egresos["cantidad"];
                        $egresos_alm["producto"]["piezas"] = $cantcalc * $egresos_alm["producto"]["cantidad"];
                    }

                    #   Existencias actuales totales
                    $existencias_totales = $productoexs->existencias;
                    $piezas_totales      = $productoexs->piezas;
                    $precio_totales      = $productoexs->existencias_precio;
                    #   Existencias actuales almacén
                    $exsalmacen          = $productoexs->existenciasAlmacen;
                    // return $exsalmacen;
                    $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
                    $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
                    $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;

                    #   Nuevas Existencias totales
                    $existencias_totales -= $egresos_alm["producto"]["cantidad"];
                    $piezas_totales      -= $egresos_alm["producto"]["piezas"];
                    $precio_totales      -= $request->precio;
                    #   Nuevas Existencias almacén
                    $existencias_almacen -= $egresos_alm["producto"]["cantidad"];
                    $piezas_almacen      -= $egresos_alm["producto"]["piezas"];
                    $precio_almacen      -= $request->precio;                    

                    #   Inserta movimiento de salida
                    $creado = ESProducto::create([
                        "producto_id"         => $productoexs->id,
                        "almacen_id"          => $egresos_alm["almacen"]["id"],
                        "referencia"          => $pedido->folio,
                        "tipo"                => 0,
                        "cantidad"            => $egresos_alm["producto"]["cantidad"],
                        "piezas"              => $egresos_alm["producto"]["piezas"],
                        "precio"              => $egresos["precio"],
                        "piezas_totales"      => $piezas_totales,
                        "existencias_totales" => $existencias_totales,
                        "piezas_totales"      => $piezas_totales,
                        "precio_totales"      => $precio_totales,
                        "piezas_almacen"      => $piezas_almacen,
                        "existencias_almacen" => $existencias_almacen,
                        "precio_almacen"      => $precio_almacen,
                        "usuario_registra"    => $usuario_id
                    ]);

                    #   Actualiza o inserta existencias almacén
                    ExistenciasAlmacen::updateOrCreate([
                        "producto_id" => $productoexs->id,
                        "almacen_id"  => $egresos_alm["almacen"]["id"],
                    ],[
                        "producto_id" => $productoexs->id,
                        "almacen_id"  => $egresos_alm["almacen"]["id"],
                        "piezas"      => $piezas_almacen,
                        "existencias" => $existencias_almacen,
                        "precio"      => $precio_almacen
                    ]);

                    #   Inserta existencias en almacenes
                    $almacenes = Almacen::where("status", 1) 
                                            ->get();
                    foreach ($almacenes as $almacen) {
                        $existenciasalm = ExistenciasAlmacen::where("almacen_id", $almacen->id)
                                                            ->where("producto_id", $productoexs->id)
                                                            ->get()
                                                            ->first();
                        ExistenciasMovimiento::create([
                            "producto_id"   => $productoexs->id,
                            "almacen_id"    => $almacen->id,
                            "movimiento_id" => $creado->id,
                            "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                            "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                            "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                        ]);
                    }

                    #   Actualiza cantidad surtida
                    $pedido_cuerpo = PedidoCuerpo::find( $egresos["id"] );
                    #   Suma cantidad y piezas surtidas
                    $cantidad_surt = ( $pedido_cuerpo->cantidad_surt + $egresos_alm["producto"]["cantidad"] );
                    $piezas_surt   = ( $pedido_cuerpo->piezas_surt + $egresos_alm["producto"]["piezas"] );

                    $pedido_cuerpo->fill([
                        "cantidad_surt" => $cantidad_surt,
                        "piezas_surt"   => $piezas_surt,
                    ])->save();

                    #   Actualiza existencias totales
                    Producto::where("id", $productoexs->id)->update([
                        "existencias"        => $existencias_totales,
                        "piezas"             => $piezas_totales,
                        "existencias_precio" => $precio_totales
                    ]);
                    
                    
                    #   Si algun producto no ha sido surtido totalmente, cambia la bandera a falso
                    if( $pedido_cuerpo->cantidad > $cantidad_surt ){
                        $surtido = false;
                    }                    
                }
            }

            #   Actualiza status de órden de compra
            if( $surtido ){
                Pedido::where("id", $id)->update(["status" => 1]);
            }

            # Response
            return response()->json([
                "head" => "success",
                "body" => [ "pedido" => $pedido ]
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
     * Surtir pedido Completo
     *
     * @return void
     */
    public function surtirPedidoCompleto( Request $request, $id){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   Consulta orden de compra
            $pedido = Pedido::with("cuerpo.producto")
                            ->find($id);

            //return $pedido;

            $almacen = Almacen::where("clave", "BOD")->first();

            #   Itera registros
            foreach ( $pedido->cuerpo as $index => $cuerpo ) {
                #   Producto de la bd con existencias en almacén
                $productoexs = Producto::with(["existenciasAlmacen"  => function ($query) use( $almacen ) {
                    $query->where("almacen_id", $almacen->id );
                }])->find( $cuerpo->producto->id );

                #   Calcula cantidad y piezas de salida
                $cant_salida = $cuerpo->cantidad - $cuerpo->cantidad_surt;
                $pzas_salida = $cuerpo->piezas - $cuerpo->piezas_surt;

                #   Existencias actuales totales
                $existencias_totales = $productoexs->existencias;
                $piezas_totales      = $productoexs->piezas;
                $precio_totales      = $productoexs->existencias_precio;
                #   Existencias actuales almacén
                $exsalmacen          = $productoexs->existenciasAlmacen;
                $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
                $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
                $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;

                #   Nuevas Existencias totales
                $existencias_totales -= $cant_salida;
                $piezas_totales      -= $pzas_salida;
                $precio_totales      -= $cuerpo->precio;
                #   Nuevas Existencias almacén
                $existencias_almacen -= $cant_salida;
                $piezas_almacen      -= $pzas_salida;
                $precio_almacen      -= $cuerpo->precio;      
                    
                #   Inserta movimiento de salida
                $creado = ESProducto::create([
                    "producto_id"         => $cuerpo->producto->id,
                    "almacen_id"          => $almacen->id,
                    "referencia"          => $pedido->folio,
                    "tipo"                => 0,
                    "cantidad"            => $cant_salida,
                    "piezas"              => $pzas_salida,
                    "precio"              => $cuerpo->precio,
                    "piezas_totales"      => $piezas_totales,
                    "existencias_totales" => $existencias_totales,
                    "piezas_totales"      => $piezas_totales,
                    "precio_totales"      => $precio_totales,
                    "piezas_almacen"      => $piezas_almacen,
                    "existencias_almacen" => $existencias_almacen,
                    "precio_almacen"      => $precio_almacen,
                    "usuario_registra"    => $usuario_id
                ]);

                #   Actualiza o inserta existencias almacén
                ExistenciasAlmacen::updateOrCreate([
                    "producto_id" => $cuerpo->producto->id,
                    "almacen_id"  => $almacen->id,
                ],[
                    "producto_id" => $cuerpo->producto->id,
                    "almacen_id"  => $almacen->id,
                    "piezas"      => $piezas_almacen,
                    "existencias" => $existencias_almacen,
                    "precio"      => $precio_almacen
                ]);

                #   Inserta existencias en almacenes
                $almacenes = Almacen::where("status", 1) 
                                        ->get();
                foreach ($almacenes as $alm) {
                    $existenciasalm = ExistenciasAlmacen::where("almacen_id", $alm->id)
                                                        ->where("producto_id", $cuerpo->producto->id)
                                                        ->get()
                                                        ->first();
                    ExistenciasMovimiento::create([
                        "producto_id"   => $productoexs->id,
                        "almacen_id"    => $alm->id,
                        "movimiento_id" => $creado->id,
                        "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                        "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                        "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                    ]);
                }

                #   Actualiza cantidad surtida
                $pedido_cuerpo = PedidoCuerpo::find( $cuerpo->id );
                #   Suma cantidad y piezas surtidas
                $cantidad_surt = ( $pedido_cuerpo->cantidad_surt + $cant_salida );
                $piezas_surt   = ( $pedido_cuerpo->piezas_surt + $pzas_salida );

                $pedido_cuerpo->fill([
                    "cantidad_surt" => $cantidad_surt,
                    "piezas_surt"   => $piezas_surt,
                ])->save();

                #   Actualiza existencias totales
                Producto::where("id", $cuerpo->producto->id )->update([
                    "existencias"        => $existencias_totales,
                    "piezas"             => $piezas_totales,
                    "existencias_precio" => $precio_totales
                ]);                 
                
            }

            $pedido->status = 1;
            $pedido->save();

            # Response
            return response()->json([
                "head" => "success",
                "body" => [ "pedido" => $pedido ]
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
     * Agrega pago a un pedido
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function agregaPago(Request $request, $id){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   Consulta pedido
            $pedido = Pedido::find( $id );

            $creado = PedidoPago::create([
                "pedido_id"        => $id,
                "forma_pago"       => "",
                "importe"          => $request->importe,
                "usuario_registra" => $usuario_id
            ]);

            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldo( $pedido->cliente_id );
            #   Inserta pago cliente
            PagoCliente::create([
                "cliente_id"       => $pedido->cliente_id,
                "pedido_id"        => $id,
                "pedido_pago_id"   => $creado->id,
                "referencia"       => $pedido->folio,
                "observaciones"    => "ABONO",
                "importe"          => $request->importe,
                "saldo"            => ( $saldo - $request->importe ),
                "usuario_registra" => $usuario_id
            ]);

            #   Actualiza saldo pendiente del cliente
            Cliente::where("id", $pedido->cliente_id)->update([
                "saldo_pendiente" => ( $saldo - $request->importe)
            ]);
            
             # Response
             return response()->json([
                "head" => "success",
                "body" => [ "pago" => $creado ]
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
     * Crea un nuevo folio consultando el último registro de un vendedor
     * para generar un consecutivo y formatearlo
     *
     * @param int $cliente_id
     * @return string $folio
     */
    private function nuevoFolio( $cliente_id ){
        $cliente = Cliente::with("usuario")->find( $cliente_id );

        $ultimo = Pedido::where("vendedor_id", $cliente->usuario_id )
                            ->latest("id")
                            ->pluck('folio');

        $ultimo = $ultimo ? ((int) explode("-", $ultimo)[1] + 1) : 1;      
        
        $pre   = $cliente->usuario ? $cliente->usuario->clave : "GEN";
        $folio = $pre . "-" . str_pad($ultimo, 7, "0", STR_PAD_LEFT);
        return $folio;
    }

    public function prueba(){
        $pedidos = Pedido::with("pagos")
                        ->get();

        foreach ($pedidos as $pedido) {
            
            $saldo_actual = $this->obtenerSaldo( $pedido->cliente_id );
            

            foreach ($pedido->pagos as $idx => $pago) {
                // CARGO
                if($idx == 0){
                    PagoCliente::create([
                        "cliente_id"       => $pedido->cliente_id,
                        "pedido_id"        => $pedido->id,
                        "pedido_pago_id"   => 0,
                        "referencia"       => $pedido->folio,
                        "observaciones"    => "CARGO",
                        "importe"          => 0 - $pedido->total,
                        "saldo"            => $saldo_actual + $pedido->total,
                        "created_at"       => $pedido->created_at,
                        "updated_at"       => $pedido->created_at,
                        "usuario_registra" => $pedido->usuario_registra
                   ]);
                }

                // ABONO
                $saldo_actual = $this->obtenerSaldo( $pedido->cliente_id );
                PagoCliente::create([
                    "cliente_id"       => $pedido->cliente_id,
                    "pedido_id"        => $pedido->id,
                    "pedido_pago_id"   => $pago->id,
                    "referencia"       => $pedido->folio,
                    "observaciones"    => $idx == 0 ? "PAGO INICIAL" : "ABONO",
                    "importe"          => $pago->importe,
                    "saldo"            => $saldo_actual - $pago->importe,
                    "usuario_registra" => $pago->usuario_registra,
                    "created_at"       => $pago->created_at,
                    "updated_at"       => $pago->created_at,
               ]);
            }

            // dd($pedido->pagado());
            if( $pedido->status == -1){
                $saldo_actual = $this->obtenerSaldo( $pedido->cliente_id );
                $restante     = $pedido->total - $pedido->pagado(); 

                PagoCliente::create([
                    "cliente_id"       => $pedido->cliente_id,
                    "pedido_id"        => $pedido->id,
                    "pedido_pago_id"   => 0,
                    "referencia"       => $pedido->folio,
                    "observaciones"    => "PEDIDO CANCELADO",
                    "importe"          => $restante,
                    "saldo"            => $saldo_actual - $restante,
                    "usuario_registra" => $pedido->usuario_registra,
                    "created_at"       => $pedido->updated_at,
                    "updated_at"       => $pedido->updated_at,
               ]);
            }
        }
        
        
        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            $saldo_actual = $this->obtenerSaldo( $cliente->id );
            $cliente = Cliente::where("id", $cliente->id)
                            ->update(["saldo_pendiente" => $saldo_actual]);
        }
    }
}
  