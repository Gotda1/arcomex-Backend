<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\Cotizacion;
use App\CotizacionAdjunto;
use App\CotizacionCuerpo;
use App\CotizacionObservacion;
use App\DatosEmpresa;
use App\ESProducto;
use App\FormasPago;
use App\Http\Requests\GuardarCotizacionRequest;
use App\Http\Requests\PasarAPedidoRequest;
use App\Mail\NuevoPedidoMailer;
use App\PagoCliente;
use App\Pedido;
use App\PedidoAdjunto;
use App\PedidoCuerpo;
use App\PedidoDireccion;
use App\PedidoPago;
use App\Producto;
use App\Prospecto;
use App\Usuario;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CotizacionController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt")->except([]);

        Relation::morphMap([
            'clientes' => Cliente::class,
            'prospectos' => Prospecto::class,
        ]);
         
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
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            #   cotizaciones prospectos
            $cotprospectos = Cotizacion::with( "prospecto:id,nombre" )
                                ->with("usuario:id,clave,nombre")
                                ->with("pedido:id,folio")
                                ->where("catalogo", "prospectos")
                                ->whereBetween("created_at", [$fechainicio, $fechafin]);

            #   Si no tiene permisos para ver todos los prospectos
            #   filtra por clientes asignados
            if(!$this->tienePermiso( "ALLPRSP" )){
                $cotprospectos->whereIn("adquisidor_id", function ($q) use ($usuario_id) {
                    $q->select("id");
                    $q->from("prospectos");
                    $q->where("usuario_id", $usuario_id);
                });
            }


            #   cotizaciones clientes
            $cotclientes = Cotizacion::with( "cliente:id,clave,nombre" )
                                ->with("usuario")
                                ->with("pedido:id,folio")
                                ->where("catalogo", "clientes")
                                ->whereBetween("created_at", [$fechainicio, $fechafin]);

            $cotizaciones = $cotprospectos->get()->merge($cotclientes->get());

            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "cotizaciones" => $cotizaciones,
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
                    "productos"   => $productos
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
    public function store(GuardarCotizacionRequest $request)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"]; 
            $data = $request->validated();

            #   Busca el adquisidor en su catálogo para encotrar el vendedor
            $adquisidor = ( $data["catalogo"] == "clientes" ) ? 
                        Cliente::find( $data["adquisidor_id"] ) :
                        Prospecto::find( $data["adquisidor_id"] );

            $creado = Cotizacion::create([ 
                "adquisidor_id"    => $data["adquisidor_id"],
                "vendedor_id"      => $adquisidor->usuario_id,
                "forma_pago"       => $data["forma_pago"],
                "localidad"        => $data["localidad"],
                "folio"            => $this->nuevoFolioCotizacion( $data["catalogo"], $data["adquisidor_id"]),
                "catalogo"         => $data["catalogo"],
                "suma"             => 0,
                "iva"              => $data["iva"] === true ? 1 : 0,
                "total"            => 0,
                "observaciones"    => $data["observaciones"],
                "tiempo_entrega"   => $data["tiempo_entrega"],
                "vigencia"         => $data["vigencia"],
                "status"           => 0,
                "usuario_registra" => $usuario_id
            ]);

            $suma = 0;
            foreach ($data["cuerpo"] as $i => $producto) {
                CotizacionCuerpo::create([
                    "cotizacion_id" => $creado->id,
                    "producto_id"   => $producto["producto"]["id"],
                    "cantidad"      => $producto["cantidad"],
                    "descripcion"   => $producto["descripcion"],
                    "piezas"        => $producto["piezas"],
                    "precio_lista"  => $producto["precio_lista"],
                    "descuento"     => $producto["descuento"] ? $producto["descuento"] : 0,
                    "precio"        => $producto["precio_uni"],
                    "orden"         => $i
                ]);

                $suma += ($producto["precio_uni"] * $producto["cantidad"]);
            }


            
            #   Actualiza cotización con la suma y el total
            $suma = round($suma, 2);
            $total = $creado->iva == 1 ? $this->sumaIVA( $suma ) : $suma;
            Cotizacion::where( "id", $creado->id )->update( [
                "suma"  => $suma,
                "total" => $total
            ]);

            # Guarda imágenes
            foreach (request("adjuntos") as $imagen) {
                CotizacionAdjunto::create([
                    "cotizacion_id" => $creado->id,
                    "adjunto"       => $this->almacenaImagenB64( "cotizaciones", $creado->folio, $imagen["source"] ),
                    "descripcion"   => $imagen["descripcion"]
                ]);
            }

            # Response
            return response()->json([
               "head" => "success",
                "body" => [ "cotizacion" => $creado ]
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
                "cotizacion"   => Cotizacion::with("cuerpo.producto.unidad")
                                    ->with("adquisidor")
                                    ->with("usuario")
                                    ->with("adjuntosimg")
                                    ->find($id),
            ];
            $pdf = app('dompdf.wrapper');
            $pdf->loadView("documentos.cotizacion", $data )
                            ->setPaper('letter');
            return $pdf->stream('cotizacion.pdf');
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
        try {
            $cotizacion = Cotizacion::with("cuerpo.producto.unidad")
                                ->with("adquisidor")
                                ->with("usuario")
                                ->with("adjuntosimg")
                                ->find($id);
    
           # Response
           return response()->json([
                "head" => "success",
                "body" => [ 
                    "cotizacion"   => $cotizacion,
                    "adquisidores" => $cotizacion->catalogo == "prospectos" ? 
                                        $this->getProspectos() : 
                                        $this->getClientes()
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
     * Update the specified resource in storage.
     * Pasa una cotización a pedido
     *
     * @param  \Illuminate\Http\Request\GuardarCotizacionRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarCotizacionRequest $request, $id)
    {
        try {
            #   Data de actualización
            $data = $request->validated();  
            $cotizacion = Cotizacion::find( $id );      

            $suma = 0;            
            #   Elimina cotización y vuelve a inserta
            CotizacionCuerpo::where("cotizacion_id", $id)->delete();
            foreach ($request["cuerpo"] as $i => $producto) {
                CotizacionCuerpo::create([
                    "cotizacion_id" => $id,
                    "producto_id"   => $producto["producto"]["id"],
                    "descuento"     => $producto["descuento"] ? $producto["descuento"] : 0,
                    "cantidad"      => $producto["cantidad"],
                    "descripcion"   => $producto["descripcion"],
                    "piezas"        => $producto["piezas"],
                    "precio_lista"  => $producto["precio_lista"],
                    "precio"        => $producto["precio_uni"],
                    "orden"         => $i
                ]);

                $suma += ($producto["precio_uni"] * $producto["cantidad"]);
            }

            #   Calcula cotización suma y total
            $iva   = in_array($data["iva"], [true, 1]) ? 1 : 0;
            $suma  = round($suma, 2);
            $total = $iva == true ? $this->sumaIVA( $suma ) : $suma;     

            #   Actualiza cotización
            Cotizacion::where("id", $id)->update([
                "adquisidor_id"    => $data["adquisidor_id"],
                "forma_pago"       => $data["forma_pago"],
                "localidad"        => $data["localidad"],
                "catalogo"         => $data["catalogo"],
                "suma"             => $suma,
                "total"            => $total,
                "iva"              => $iva,
                "observaciones"    => $data["observaciones"],
                "tiempo_entrega"   => $data["tiempo_entrega"],
                "vigencia"         => $data["vigencia"],
            ]);    
            
            #   Borra imagenes que ya no se encuentran en el array
            $adjuntosserv = request("adjuntosserv");
            $adjborrar = CotizacionAdjunto::where( "cotizacion_id", $id )
                                            ->whereNotIn("id", $adjuntosserv )
                                            ->get();            
            foreach ( $adjborrar as $adjunto ) {
                #   Borra de la bd
                CotizacionAdjunto::destroy( $adjunto->id );
                #   Borra del archivo
                $path = "cotizaciones/" . $cotizacion->folio . "/" . $adjunto->adjunto;
                if (Storage::exists( $path )) 
                    Storage::delete($path);
                
            }

            #   Actualiza descripción de imagenes que se encuentran en el array

            foreach ($adjuntosserv as $adjunto) {
                CotizacionAdjunto::where( "cotizacion_id", $id )->update([
                    "descripcion"   => $adjunto["descripcion"]
                ]);
            }

            # Guarda registro de imágenes
            foreach (request("adjuntos") as $imagen) {
                CotizacionAdjunto::create([
                    "cotizacion_id" => $id,
                    "adjunto"       => $this->almacenaImagenB64( "cotizaciones", $cotizacion->folio, $imagen["source"] ),
                    "descripcion"   => $imagen["descripcion"]
                ]);
            }

            # Guarda archivo imágenes
            $path = "public/cotizaciones/" . $cotizacion->folio;
            foreach ($_FILES as $file => $value) {
                $upload = $request->file($file)->store($path);  
                CotizacionAdjunto::create([
                    "cotizacion_id" => $id,
                    "adjunto" => str_replace($path . "/", "", $upload)
                ]);
            }

            # Response
            return response()->json([
               "head" => "success",
                "body" => [ "cotizacion" => Cotizacion::find( $id ) ]
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
    
    public function aPedido(PasarAPedidoRequest $request,  $id){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"]; 
    
            #   Consulta cotización a convertir
            $cotizacion = Cotizacion::find($id);
            $cuerpo     = CotizacionCuerpo::where("cotizacion_id" , $id)->get();
    
            #   Si la cotización fué a un prospecto, lo convierte a cliente
            if ( $cotizacion->catalogo == "prospectos" ){
                $prospecto = Prospecto::find($cotizacion->adquisidor_id);
                #   Array de insersión con data de prospecto y nueva clave
                $dataInsert = array_merge($prospecto->toArray(), [ 
                    "clave"            => $this->armaClaveCliente( $prospecto->clasificacion_clave ),
                    "usuario_registra" => request("usuarioDB")["id"]
                ]);            
                #   Crea nuevo cliente
                $cliente = Cliente::create($dataInsert);
                #   Borra prospecto
                Prospecto::destroy( $prospecto["id"] );
                #   Cambia el catalogo de la cotizacion a clientes
                Cotizacion::where("id", $id)->update([
                    "catalogo" => "clientes",
                    "adquisidor_id" => $cliente->id
                ]);
                #   Asigna el id del cliente creado al adquisidor_id
                $cotizacion->adquisidor_id = $cliente->id;
            }
    
            #   Guarda encabezado
            $data = $request->validated();
            $creado = Pedido::create([
                "vendedor_id"            => $cotizacion->vendedor_id,
                "cliente_id"             => $cotizacion->adquisidor_id,
                "folio"                  => $this->nuevoFolioPedido( $cotizacion->adquisidor_id ),
                "observaciones"          => $data["observaciones"],
                "iva"                    => $cotizacion->iva,
                "suma"                   => $cotizacion->suma,
                "total"                  => $cotizacion->total,
                "observaciones_internas" => $data["observaciones_internas"],
                "status"                 => 0,
                "usuario_registra"       => $usuario_id
            ]);
            
            #   Guarda cuerpo
            foreach ($cuerpo as $item) {
                PedidoCuerpo::create([
                    "pedido_id"    => $creado->id,
                    "producto_id"  => $item->producto_id,
                    "cantidad"     => $item->cantidad,
                    "descripcion"  => $item->descripcion,
                    "piezas"       => $item->piezas,
                    "precio"       => $item->precio,
                    "descuento"    => $item->descuento ? $item->descuento : 0,
                    "precio_lista" => $item->precio_lista
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

            PedidoDireccion::create($dataInsertDir);
    
             # Guarda imágenes
             foreach (request("adjuntos") as $imagen) {
                PedidoAdjunto::create([
                    "pedido_id" => $creado->id,
                    "adjunto"       => $this->almacenaImagenB64( "pedidos", $creado->folio, $imagen["source"] ),
                    "descripcion"   => $imagen["descripcion"]
                ]);
            }
    
            #   Guardar Pago
            $pago = PedidoPago::create([
                "pedido_id"        => $creado->id,
                "forma_pago"       => $cotizacion->forma_pago,
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
                "importe"          => 0 - $cotizacion->total,
                "saldo"            => ( $saldo + $cotizacion->total ),
                "usuario_registra" => $usuario_id
            ]);

            #   Inserta abono
            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldo( $creado->cliente_id );
            #   Inserta pago
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
            
            #   Actualiza cotización
            Cotizacion::where("id", $id)->update([
                "status"    => 1,
                "pedido_id" => $creado->id
            ]);
            
            #   Notifica a almacén
            $destinatarios = DB::table("usuarios")->whereIn("rol", ["ALMC", "COMP"])->get()->pluck("email");
            Mail::to($destinatarios)->send(new NuevoPedidoMailer($creado->id));
            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["pedido" => $creado ]
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            #   Consulta cotización a borrar
            $cotizacion = Cotizacion::find($id);    
            #   Elimina cotización y sus tablas hijas 
            Cotizacion::destroy($id);
            DB::table("cotizacion_cuerpo")->where("cotizacion_id", $id)->delete();
            DB::table("cotizacion_observaciones")->where("cotizacion_id", $id)->delete();
            DB::table("cotizacion_adjuntos")->where("cotizacion_id", $id)->delete();
           
            #   Elimina imagenes del ticket
            $path = "cotizaciones/" . $cotizacion->folio . "/";
            if (Storage::exists( $path )) 
                Storage::deleteDirectory($path);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["cotizacion" => $cotizacion]
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
     * Cancelar una cotización
     *
     * @return void
     */
    public function cancelar( $id ){
        try {
            #   Consulta cotizacion a actualizar 
            $cotizacion = Cotizacion::find($id);
            #   Actualiza status
            Cotizacion::where("id", $id)->update(["status" => -1]);

            # Response
            return response()->json([
                "head" => "success",
                 "body" => [ "cotizacion" => $cotizacion ]
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
     * Obtener catalogos de adquisidores
     */
    public function catAdquisodores( $catalogo ){
        try {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [  "adquisidores"  =>  $catalogo == "prospectos" ? 
                                                 $this->getProspectos() : 
                                                 $this->getClientes() ]
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
     * Lista observaciones de una cotización
     *
     * @return response obsevaciones
     */
    public function listarObservaciones(){
        try {
            $cotizacion_id = request("cotizacion_id");

            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "observaciones_seg" => CotizacionObservacion::with("usuario")
                    ->where("cotizacion_id", $cotizacion_id)
                    ->orderBy("id", "desc")
                    ->get()
                ]
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
     * Guarda observación de cotización
     *
     * @param Request $request
     * @return void
     */
    public function guardarObservacion(Request $request){
        try {
            $cotizacion_id = request("cotizacion_id");

            $creado = CotizacionObservacion::create([
                "cotizacion_id"    => $cotizacion_id,
                "observacion"      => $request->observacion,
                "usuario_registra" => $request->usuarioDB["id"]
            ]);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "observacion" => $creado
                ]
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
     * Duplica una cotización
     *
     * @param int $id
     * @return void
     */
    public function duplicate( $id ){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"]; 

            $cotizacion = Cotizacion::find( $id );
            $cotizacion_cuerpo = CotizacionCuerpo::where("cotizacion_id", $id)
                                                ->get();

            $cotizacion_adjuntos = CotizacionAdjunto::where("cotizacion_id", $id)
                                                ->get();
            #   Duplica encabezado
            $nueva = $cotizacion->replicate()
                        ->fill([
                            "folio"            => $this->nuevoFolioCotizacion( $cotizacion->catalogo, $cotizacion->adquisidor_id ),
                            "pedido_id"        => 0,
                            "status"           => 0,
                            "usuario_registra" => $usuario_id
                        ]);
            $nueva->save();
            

            #   Duplica cuerpo
            foreach ( $cotizacion_cuerpo as $cuerpo ) {
                $producto = Producto::find( $cuerpo->producto_id );
                if(!$producto) continue;
                
                CotizacionCuerpo::create(array_merge($cuerpo->toArray(), [
                    "cotizacion_id"    => $nueva->id
                ]));
            }

            #   Duplica adjuntos
            foreach ( $cotizacion_adjuntos as $adjunto ) {
                $nombre = uniqid() . ".png";
                Storage::copy("cotizaciones/$cotizacion->folio/$adjunto->adjunto", "cotizaciones/$nueva->folio/$nombre");
                CotizacionAdjunto::create([
                    "cotizacion_id" => $nueva->id,
                    "adjunto"       => $nombre,
                    "descripcion"   => $adjunto->descripcion,
                ]);
            }

            # Response
            return response()->json([
                "head" => "success",
                 "body" => [ "cotizacion" => $nueva ]
            ], 200);
            
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
     * Consulta catálogo de prospectos
     *
     * @return collection prospectos
     */
    private function getProspectos(){
         #   id requester
         $usuario_id = request("usuarioDB")["id"];           
         #   permiso para ver todos los prospectos
         $ALLPRSP = $this->tienePermiso( "ALLPRSP" );
         #   prospectos
         $prospectos = Prospecto::select("id", "clasificacion_clave", "nombre")
                             ->with("clasificacion:clave,nombre")
                             ->where("status", 1)
                             ->orderBy("nombre", "asc");

         if(!$ALLPRSP) $prospectos->whereIn("usuario_id", [0, $usuario_id]);

         return $prospectos->get();
    }

    /**
     * Consulta catálogo de clientes
     *
     * @return collection clientes
     */
    private function getClientes(){
         #   id requester
         $usuario_id = request("usuarioDB")["id"];           
         #   permiso para ver todos los clientes
         $ALLCLT = $this->tienePermiso( "ALLCLT" );
         #   clientes
         $clientes = Cliente::select("id", "clave", "clasificacion_clave", "nombre")
                             ->with("clasificacion:clave,nombre")
                             ->where("status", 1)
                             ->orderBy("nombre", "asc");

         if(!$ALLCLT) $clientes->whereIn("usuario_id", [0, $usuario_id]); 

         return $clientes->get();
    }

    /**
     * Crea un nuevo folio consultando el último registro 
     * para generar un consecutivo y formatearlo
     *
     * @return void
     */
    private function nuevoFolioCotizacion( $catalogo, $adquisidor_id ){
        $adquisidor = ( $catalogo == "clientes" ) ? 
                        Cliente::with("usuario")->find( $adquisidor_id ) :
                        Prospecto::with("usuario")->find( $adquisidor_id );

        $ultimo = Cotizacion::where("vendedor_id", $adquisidor->usuario_id )
                                ->pluck('folio')
                                ->last();
                            
        $ultimo = $ultimo ? ((int) explode("-", $ultimo)[1] + 1) : 1;      

        $pre    = $adquisidor->usuario ? $adquisidor->usuario->clave : "GEN";
        $folio  = $pre . "-" . str_pad($ultimo, 7, "0", STR_PAD_LEFT);
        return $folio;
    }

     /**
     * Crea un nuevo folio consultando el último registro de un vendedor
     * para generar un consecutivo y formatearlo
     *
     * @param int $cliente_id
     * @return string $folio
     */
    private function nuevoFolioPedido( $cliente_id ){
        $cliente = Cliente::with("usuario")->find( $cliente_id );

        $ultimo = Pedido::where("vendedor_id", $cliente->usuario_id )
                            ->latest("id")
                            ->pluck('folio');

        $ultimo = $ultimo ? ((int) explode("-", $ultimo)[1] + 1) : 1;      
        
        $pre   = $cliente->usuario ? $cliente->usuario->clave : "GEN";
        $folio = $pre . "-" . str_pad($ultimo, 7, "0", STR_PAD_LEFT);
        return $folio;
    }
}
