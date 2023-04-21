<?php

namespace App\Http\Controllers;

use App\ClasificacionAdquisidor;
use App\Http\Requests\GuardarClienteRequest;
use App\ClasificacionCliente;
use App\Cliente;
use App\Usuario;
use Exception;

class ClienteController extends Controller
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
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   permiso para ver todos los clientes
            $ALLCLT = $this->tienePermiso( "ALLCLT" );

            #   Clientes
            $query = Cliente::with("clasificacion:clave,nombre")
                                ->with("usuario:id,nombre");

            if(!$ALLCLT) $query->whereIn("usuario_id", [0, $usuario_id]);


            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "clientes" => $query->get()
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
                    "clasificaciones" => ClasificacionAdquisidor::select("clave", "nombre")->get(),
                    "usuarios"        => Usuario::select("id","clave","nombre")
                                                  ->where("rol", "VTAS")
                                                  ->where("status", 1)
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
    public function store(GuardarClienteRequest $request)
    {
        try {            
            #   Array de inserción
            $dataInsert = array_merge($request->validated(), [
                "clave"            => $this->armaClaveCliente( request("clasificacion_clave") ),
                "usuario_id"       => request("usuario_id") ? request("usuario_id") : $request->usuarioDB["id"],
                "usuario_registra" => $request->usuarioDB["id"]
            ]);

            #   Crea cliente
            $creado = Cliente::create($dataInsert);
            
            # Response
            return response()->json([
                "head" => "success",
                "body" => ["cliente" => $creado]
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
                    "cliente" => Cliente::with("clasificacion:id,clave,nombre")
                                            ->with("usuario:id,clave,nombre")
                                            ->find($id)
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
                    "cliente" => Cliente::find($id),
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
     * @param  \App\Http\Requests\GuardarClienteRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarClienteRequest $request, $id)
    {
        try {
            #   Si cambió la clasificación del cliente, cambia la clave
            $dataUpdate = $request->validated();
            $clasf = Cliente::find($id)->pluck("clasificacion_clave");
            if ($clasf != $request->clasificacion_clave){
                $nueva = $this->armaClaveCliente( $dataUpdate["clasificacion_clave"] );
                $dataUpdate["clave"] = $nueva;
            }

            #   Actualiza registro
            Cliente::where("id", $id)->update( $dataUpdate );      
            
            #   Consulta el registro actualizado
            $actualizado = Cliente::find($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["cliente" => $actualizado]
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            #   Consulta cliente a borrar
            $cliente = Cliente::find($id);
            #   Borra cliente
            Cliente::destroy($id);
            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["cliente" => $cliente]
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
