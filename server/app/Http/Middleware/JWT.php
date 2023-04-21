<?php

namespace App\Http\Middleware;

use App\Usuario;
use Closure;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permiso = null)
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();
            // $p = $this->permiso($usuario, $permiso);
            $user = Usuario::with("rol")->find($usuario->id)->toArray();
            // $request->request->add(['usuarioDB' => $user]);
            if ($user && $user["status"] == 1) {
                $request->request->add(['usuarioDB' => $user]);
            } else {
                return response()->json(['status' => 'Authorization Denied'],403);
            }
        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid']);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => 'Token is Expired'],403);
            } else {
                return response()->json(['status' => 'Authorization Token not found'],403);
            }
        }
        return $next($request);
    }

    public function permiso($usuario, $permiso)
    {
        $user = Usuario::find($usuario->id);
        // ::with('rol.permisos')
        //     ->with("papa.papa")
        //     ->with("hijos.hijos")
        //     ->find($usuario->id)
        //     ->toArray();

        // $permisos = $user["rol"]["permisos"];
        // foreach ($permisos as $p) {
        //     if ($p["clave"] == $permiso) {
        //         return $user;
        //     }
        // }

        return false;
    }
}
