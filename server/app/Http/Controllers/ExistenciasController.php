<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\ExistenciasMovimiento;
use App\Producto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExistenciasController extends Controller
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
            #   Consulta últimos movimientos de esa fecha
            $ultimos = DB::table("existencias_movimiento")
            ->select(DB::raw("max(movimiento_id) as max"))
            ->where("created_at", "<=", request("fecha") ." 23:59:59")
            ->groupBy("producto_id")
            ->get()->pluck("max");
            
            
            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "exsproductos" => Producto::with(["existenciasMov" => function ($query) use($ultimos){
                        $query->whereIn("movimiento_id", $ultimos);
                    }, "unidad"])->get(),
                    request("fecha") ."23:59:59"
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
                "body" => [ "almacenes" => Almacen::where("status", 1) 
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
