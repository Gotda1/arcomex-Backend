<?php

namespace App\Http\Controllers;

use App\CategoriaProducto;
use App\Http\Requests\GuardarProductoRequest;
use App\Pedido;
use App\Producto;
use App\ProveedorProducto;
use App\Unidad;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function __construct()
    {
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
            #   Status from request
            $status = request("status");

            $productos = Producto::with(["unidad", "categoriaProducto"]);
            if($status != "ALL"){
                $productos = $productos->where("status", $status);
            }

            $productos = $productos->with("enPedidos")
                                    ->get();

            # Response
            return response()->json([
                "head" => "success",
                "body" => [ 
                    "productos"   => $productos
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
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "unidades"             => Unidad::all(),
                    "categorias_productos" => CategoriaProducto::where("status",1)
                                                                ->orderBy("orden")
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
     * @param  \Illuminate\Http\GuardarProductoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GuardarProductoRequest $request)
    {
        try {          
            #   Array de inserción
            $dataInsert = array_merge( $request->validated(), [
                "usuario_registra"  => $request->usuarioDB["id"]
            ]);
            
            #   Crea producto
            $creado = Producto::create($dataInsert);
            
            # Response
            return response()->json([
                "head" => "success",
                "body" => [ "producto" => $creado ]
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
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "producto" => Producto::with(["unidad", "categoriaProducto"])->find($id)
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try { 
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "producto" => Producto::with("unidad")->find($id)
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarProductoRequest $request, $id)
    {
        try {
            #   Actualiza registro
            Producto::where( "id", $id )->update( $request->validated() );
            
            #   Consulta el registro actualizado
            $actualizado = Producto::find( $id );

            #   Response
            return response()->json([
                "head" => "success",
                "body" => [ "producto" => $actualizado ]
            ], 200);            

        } catch (\Throwable $e) {
            report($e);
            #   Response
            return response()->json([
                "head" => "error",
                "body" => [ "message" => "Error del servidor" ]
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
            #   Consulta producto a borrar
            $producto = Producto::find( $id );
            DB::table("proveedores_productos")->where("producto_id", $id)->delete();
            #   Borra producto
            Producto::destroy($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["producto" => $producto]
            ], 200);            
        } catch (\Throwable $e) {
            report($e);
            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }
}
