<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\Http\Requests\GuardarESProductoRequest;
use App\OrdenCompra;
use App\Producto;
use Exception;
use Illuminate\Http\Request;

class ESProductoController extends Controller
{
    public function __construct(){
        $this->middleware("jwt");
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
            $almacen     = request("almacen");

            $query = ESProducto::with(["producto"  => function ($query) {
                $query->select("id", "unidad_id", "clave", "nombre", "largo", "ancho");
            }, "producto.unidad" => function ($query) {
                $query->select("id", "abreviatura", "clave");
            }])->with("almacen")
            ->whereBetween("created_at", [ $fechainicio, $fechafin])
            ->where("cantidad", "<>", 0);
            

            if($almacen != "0") $query->where("almacen_id", $almacen);

            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "esproductos" => $query->get()
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        try {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "productos" => Producto::with("unidad")
                                    ->where("status", 1)
                                    ->orderBy("nombre", "asc")
                                    ->get(),
                    "almacenes" => Almacen::where("status", 1)
                                    ->get()
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
    public function store(GuardarESProductoRequest $request)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   Producto del movimiento
            $pmovimiento = request("producto");
            // return $request->validated();
            #   Producto de la bd con existencias en almacén
            $producto = Producto::with(["existenciasAlmacen"  => function ($query) use($request) {
                            $query->where("almacen_id", $request->almacen_id);
                        }])->find( $request->producto_id );

            #   Existencias actuales totales
            $existencias_totales = $producto->existencias;
            $piezas_totales      = $producto->piezas;
            $precio_totales      = $producto->existencias_precio;
            #   Existencias actuales almacén
            $exsalmacen          = $producto->existenciasAlmacen;
            $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
            $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
            $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;


            #   Si es entrada o sálida
            if ( $request->tipo === "e" ){
                #   Existencias totales
                $existencias_totales += $pmovimiento["cantidad"];
                $piezas_totales      += $pmovimiento["piezas"];
                $precio_totales      += $request->precio;
                #   Existencias almacén
                $existencias_almacen += $pmovimiento["cantidad"];
                $piezas_almacen      += $pmovimiento["piezas"];
                $precio_almacen      += $request->precio;
            }else{
                #   Existencias totales
                $existencias_totales -= $pmovimiento["cantidad"];
                $piezas_totales      -= $pmovimiento["piezas"];
                $precio_totales      -= $request->precio;
                #   Existencias almacén
                $existencias_almacen -= $pmovimiento["cantidad"];
                $piezas_almacen      -= $pmovimiento["piezas"];
                $precio_almacen      -= $request->precio;
            }
            
            #   Array de inserción
            $dataInsert = array_merge( $request->validated(), [
                "piezas_totales"      => $piezas_totales,
                "existencias_totales" => $existencias_totales,
                "piezas_totales"      => $piezas_totales,
                "precio_totales"      => $precio_totales,
                "piezas_almacen"      => $piezas_almacen,
                "existencias_almacen" => $existencias_almacen,
                "precio_almacen"      => $precio_almacen,
                "tipo"                => $request->tipo === "e" ? 1 : 0,
                "precio"              => $request->precio,
                "usuario_registra"    => $usuario_id
            ]);
                
            #   Inserta movimiento
            $creado = ESProducto::create($dataInsert);
            
            #   Actualiza existencias totales
            Producto::where("id", $request->producto_id)->update([
                            "existencias"        => $existencias_totales,
                            "piezas"             => $piezas_totales,
                            "existencias_precio" => $precio_totales
                        ]);

            #   Actualiza o inserta existencias almacén
            ExistenciasAlmacen::updateOrCreate([
                "producto_id" => $request->producto_id,
                "almacen_id"  => $request->almacen_id,
            ],[
                "producto_id" => $request->producto_id,
                "almacen_id"  => $request->almacen_id,
                "piezas"      => $piezas_almacen,
                "existencias" => $existencias_almacen,
                "precio"      => $precio_almacen
            ]);

            #   Inserta existencias en almacenes
            $almacenes = Almacen::where("status", 1)
                                 ->get();

            foreach ($almacenes as $almacen) {
                $existenciasalm = ExistenciasAlmacen::where("almacen_id", $almacen->id)
                                                    ->where("producto_id", $request->producto_id)
                                                    ->get()
                                                    ->first();
                ExistenciasMovimiento::create([
                    "producto_id"   => $request->producto_id,
                    "almacen_id"    => $almacen->id,
                    "movimiento_id" => $creado->id,
                    "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                    "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                    "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                ]);
            }
      
            #   Response
            return response()->json([
                "head" => "success",
                "body" => [ "esproducto" => $creado ]
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function showAlmacenes(){
        try {
            # Response
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
            ], 400, []);
        }
    }
}
