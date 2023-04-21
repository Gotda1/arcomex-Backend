<?php

namespace App\Http\Controllers;

use App\ClasificacionAdquisidor;
use App\Cliente;
use App\Cotizacion;
use App\Prospecto;
use App\Http\Requests\GuardarProspectoRequest;
use App\Usuario;
use Exception;

class ProspectoController extends Controller
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
            #   permiso para ver todos los prospectos
            $ALLPRSP = $this->tienePermiso( "ALLPRSP" );

            #   Prospectos
            $query = Prospecto::with("clasificacion:clave,nombre")
                                    ->with("usuario:id,nombre");

            if(!$ALLPRSP) $query->whereIn("usuario_id", [0, $usuario_id]);

            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "prospectos" => $query->get()
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
    public function store(GuardarProspectoRequest $request)
    {
        try {            
            #   Array de inserción
            $dataInsert = array_merge($request->validated(), [
                "usuario_id"        => request("usuario_id") ? request("usuario_id") : $request->usuarioDB["id"],
                "usuario_registra"  => $request->usuarioDB["id"]
            ]);

            #   Crea prospecto
            $creado = Prospecto::create($dataInsert);
            
            # Response
            return response()->json([
                "head" => "success",
                "body" => ["prospecto" => $creado]
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
                    "prospecto" => Prospecto::with("clasificacion:id,clave,nombre")
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
                    "prospecto" => Prospecto::find($id),
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
    public function update(GuardarProspectoRequest $request, $id)
    {
        try {
            #   Actualiza registro
            Prospecto::where("id", $id)->update($request->validated());
            #   Consulta el registro actualizado
            $actualizado = Prospecto::find($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["prospecto" => $actualizado]
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
            #   Consulta prospecto a borrar
            $prospecto = Prospecto::find($id);
            #   Borra prospecto
            Prospecto::destroy($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["prospecto" => $prospecto]
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
     * Convierte un prospecto a cliente
     */
    public function convertirACliente($id){
        try {
            #   Consulta registro a actualizar
            $prospecto = Prospecto::find($id);
            #   Array de insersión con data de prospecto y nueva clave
            $dataInsert = array_merge($prospecto->toArray(), [ 
                "clave"            => $this->armaClaveCliente( $prospecto->clasificacion_clave ),
                "usuario_registra" => request("usuarioDB")["id"]
            ]);
            
            #   Crea nuevo cliente
            $cliente = Cliente::create($dataInsert);

            #   Actualiza cotizaciones de los prospectos 
            #   apuntando al catálogo de clientes
            Cotizacion::where("catalogo", "prospectos")
                ->where("adquisidor_id", $id)
                ->update([
                    "adquisidor_id" => $cliente->id,
                    "catalogo"      => "clientes",
                ]);

            #   Borra prospecto
            Prospecto::destroy( $id );
            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["prospecto" => $prospecto]
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
