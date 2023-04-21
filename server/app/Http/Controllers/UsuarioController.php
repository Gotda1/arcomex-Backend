<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarUsuarioRequest;
use App\Rol;
use App\Usuario;
use Exception;
use Illuminate\Support\Facades\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt")->except("destroySessions");
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

            # Response
            return response()->json([
                "head" => "error",
                "body" => [
                    "usuarios" => Usuario::with("rol:clave,nombre")
                    ->where("id", "<>", $usuario_id)
                    ->get(),
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
                "body" => [ "roles" => Rol::all() ]
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
     * @param  \App\Http\Requests\GuardarUsuarioRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GuardarUsuarioRequest $request)
    {
        try {            
            #   Array de inserción
            $dataInsert = array_merge($request->validated(), [
                "password"          => bcrypt($request->password),
                "usuario_registra"  => $request->usuarioDB["id"]
            ]);
            
            #   Crea usuario
            $creado = Usuario::create($dataInsert);
            
            # Response
            return response()->json([
                "head" => "success",
                "body" => ["usuario" => $creado]
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
                    "usuario" => Usuario::with("rol")
                        ->find($id),
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
                "body" => [ "usuario" => Usuario::find($id) ]
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
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\GuardarUsuarioRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarUsuarioRequest $request, $id)
    {
        try {
            $dataUpdate = $request->validated();
            #   Si no viene la contraseña, la quita
            if (!$dataUpdate["password"])
                unset($dataUpdate["password"]);
            else
                $dataUpdate["password"] = bcrypt($dataUpdate["password"]);
            
            #   Actualiza registro
            Usuario::where("id", $id)->update($dataUpdate);
            #   Consulta el registro actualizado
            $actualizado = Usuario::find($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["usuario" => $actualizado]
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
            #   Consulta usuario a borrar
            $usuario = Usuario::find($id);
            #   Borra usuario
            Usuario::destroy($id);

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["usuario" => $usuario]
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


    public function destroySessions( Request $request){
        $user = JWTAuth::user();
        print( $user );
      
    }
}
