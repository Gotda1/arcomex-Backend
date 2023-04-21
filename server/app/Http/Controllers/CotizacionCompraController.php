<?php

namespace App\Http\Controllers;

use App\CotizacionCompra;
use App\CotizacionCompraAdjunto;
use App\CotizacionCompraCuerpo;
use App\CotizacionCuerpo;
use App\DatosEmpresa;
use App\Http\Requests\GuardarCotizacionCompraRequest;
use App\Proveedor;
use App\ProveedorProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CotizacionCompraController extends Controller
{
    public function __construct()
    {
       $this->middleware("jwt");
       //->except(["show"]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "cotizaciones_compra" => CotizacionCompra::with("proveedor:id,clave,nombre")
                                                                ->whereBetween("created_at", [$fechainicio, $fechafin])
                                                                ->get()
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GuardarCotizacionCompraRequest $request)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"]; 
            
            #   Guarda encabezado
            $data = $request->validated();
            $creado = CotizacionCompra::create([
                "proveedor_id"     => $data["proveedor_id"],
                "folio"            => $this->nuevoFolio($data["proveedor_id"]),
                "observaciones"    => $data["observaciones"],
                "status"           => 0,
                "usuario_registra" => $usuario_id
            ]);

            #   Guarda cuerpo
            $total = 0;
            foreach ($data["cuerpo"] as $producto) {
                CotizacionCompraCuerpo::create([
                    "cotizacion_compra_id" => $creado->id,
                    "producto_id"          => $producto["producto"]["id"],
                    "cantidad"             => $producto["cantidad"],
                    "piezas"               => $producto["piezas"],
                    "peso"                 => $producto["peso"],
                    "precio_u"             => $producto["precio_u"],
                    "total"                => $producto["total"],
                    "color"                => $producto["producto"]["color"],
                    "presupuesto"          => $producto["presupuesto"],
                    "descripcion"          => $producto["descripcion"]
                ]);
                
                $total += $producto["total"];
            }

            #   Actualiza cotización el total
            CotizacionCompra::where( "id", $creado->id )->update( [
                "total" => round($total, 2)
            ]);

            # Guarda imágenes
            foreach (request("adjuntos") as $imagen) {
                CotizacionCompraAdjunto::create([
                    "cotizacion_compra_id" => $creado->id,
                    "adjunto"              => $this->almacenaImagenB64( "cotizaciones_compra", $creado->folio, $imagen["source"] ),
                    "descripcion"          => $imagen["descripcion"]
                ]);
            }
            
            # Response
            return response()->json([
               "head" => "success",
                "body" => [ "cotizacion_compra" => $creado ]
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
                "datosempresa"      => DatosEmpresa::first(),
                "cotizacion_compra" => CotizacionCompra::with(["cuerpo.producto.unidad","proveedor", "uregistra"])
                                                            ->find($id)
            ];
            // dd($data);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView("documentos.cotizacioncompra", $data )
                            ->setPaper('letter');
            return $pdf->stream('cotizacioncomopra.pdf');
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
            $cotizacion_compra = CotizacionCompra::with(["cuerpo.producto.unidad", "proveedor", "adjuntosimg"])
                                        ->find($id);
            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "cotizacion_compra" => $cotizacion_compra,
                    "productos"         => ProveedorProducto::with("producto.unidad")
                                                        ->where("proveedor_id", $cotizacion_compra->proveedor_id)
                                                        ->get()
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarCotizacionCompraRequest $request, $id)
    {
        try {
            #   Data de actualización
            $data = $request->validated();  
            $cotizacion = CotizacionCompra::find( $id );  

            #   Elimina orden de compra cuerpo y vuelve a inserta
            $total = 0;
            CotizacionCompraCuerpo::where("cotizacion_compra_id", $id)->delete();
            foreach ($data["cuerpo"] as $producto) {
                CotizacionCompraCuerpo::create([
                    "cotizacion_compra_id" => $id,
                    "producto_id"          => $producto["producto"]["id"],
                    "cantidad"             => $producto["cantidad"],
                    "piezas"               => $producto["piezas"],
                    "peso"                 => $producto["peso"],
                    "precio_u"             => $producto["precio_u"],
                    "total"                => $producto["total"],
                    "color"                => $producto["producto"]["color"],
                    "presupuesto"          => $producto["presupuesto"],
                    "descripcion"          => $producto["descripcion"]
                ]);

                $total += $producto["total"];
            }

            #   Actualiza cotización de compra
            CotizacionCompra::where("id", $id)->update([
                "proveedor_id"    => $data["proveedor_id"],
                "observaciones"   => $data["observaciones"],
                "total"           => round($total, 2)
            ]);

             #   Borra imagenes que ya no se encuentran en el array
             $adjuntosserv = request("adjuntosserv");
             $adjborrar = CotizacionCompraAdjunto::where( "cotizacion_compra_id", $id )
                                             ->whereNotIn("id", $adjuntosserv )
                                             ->get();            
             foreach ( $adjborrar as $adjunto ) {
                 #   Borra de la bd
                 CotizacionCompraAdjunto::destroy( $adjunto->id );
                 #   Borra del archivo
                 $path = "cotizaciones_compra/" . $cotizacion->folio . "/" . $adjunto->adjunto;
                 if (Storage::exists( $path )) 
                     Storage::delete($path);
                 
             }
 
             #   Actualiza descripción de imagenes que se encuentran en el array
             foreach ($adjuntosserv as $adjunto) {
                 CotizacionCompraAdjunto::where( "cotizacion_compra_id", $id )->update([
                     "descripcion"   => $adjunto["descripcion"]
                 ]);
             }
 
             # Guarda registro de imágenes
             foreach (request("adjuntos") as $imagen) {
                 CotizacionCompraAdjunto::create([
                     "cotizacion_compra_id" => $id,
                     "adjunto"       => $this->almacenaImagenB64( "cotizaciones_compra", $cotizacion->folio, $imagen["source"] ),
                     "descripcion"   => $imagen["descripcion"]
                 ]);
             }

            # Response
            $cotizacion_compra = CotizacionCompra::find( $id );   
            return response()->json([
                "head" => "success",
                 "body" => [ "cotizacion_compra" => $cotizacion_compra ]
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
            #   Consulta cotización a borrar
            $cotizacion_compra = CotizacionCompra::find($id);    
            #   Elimina cotizacion_compra y sus tablas hijas 
            CotizacionCompra::destroy($id);
            DB::table("cotizaciones_compra_cuerpo")->where("cotizacion_compra_id", $id)->delete();
            DB::table("cotizacion_compra_adjuntos")->where("cotizacion_compra_id", $id)->delete();
           
            #   Elimina imagenes de la orden
            $path = "cotizaciones_compra/" . $cotizacion_compra->folio . "/";
            if (Storage::exists( $path )) 
                Storage::deleteDirectory($path);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["cotizacion_compra" => $cotizacion_compra]
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
    
    /*/**-----------------------
     * Función para actualizar el status de un registro
     * -1 Cancelar
     * 1 Resuelta
     *  
     *  
     *------------------------**/
    public function updateStatus( $id ){
        try {
            #   Status
            $status = request("status");
            #   Actualiza cotización de compra
            $cotizacion_compra = CotizacionCompra::where("id", $id)->update([
                "status"    => $status
            ]);

            return response()->json([
                "head" => "success",
                 "body" => [ "cotizacion_compra" => $cotizacion_compra ]
            ], 200);

        } catch (\Throwable $th) {
            report($th);
            
            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400, []);
        }
    }

    /**
     * Crea un nuevo folio consultando el último registro 
     * para generar un consecutivo y formatearlo
     *
     * @return void
     */
    private function nuevoFolio( $proveedor_id ){
        $proveedor = Proveedor::find($proveedor_id);

        $ultimo = CotizacionCompra::where(DB::raw("SUBSTRING_INDEX(folio, '-',1)"), $proveedor->clave)
                                ->pluck('folio')->last();

        $ultimo = $ultimo ? ((int) explode("-", $ultimo)[1] + 1) : 1;        
        
        $ultimo = str_pad($ultimo, 5, "0", STR_PAD_LEFT);
       
        return $proveedor->clave . "-" . $ultimo;
    }
}
