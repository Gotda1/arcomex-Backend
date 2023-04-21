<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Usuario;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt", ["except" => ["login"]]);
    }

    public function register(LoginRequest $request)
    {
        return $request;
    }

    public function index()
    {
    }

    public function login(LoginRequest $request)
    {
        try {
            #   Credenciales
            $credentials = ["email" => $request->email, "password" => $request->password, "status" => 1];
            #   Verifica credenciales en BD
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    "head" => "error",
                    "body" => [
                        "error" => "invalid_credentials",
                        "message" => "Usuario y/o contraseña inválidos",
                    ]
                ], 400);
            }

            $usuario = Usuario::with("rol.permisos:clave")->where("id", Auth::id())->first();
            $usuario["dominio"] = [
                "prefijo" => "MRS"
            ];
            
            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    'usuario' => $usuario,
                    'access_token' => $token,
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ], 200);
        } catch (\Throwable $e) {
            report($e);
            
            return response()->json([
                "head" => "error",
                "body" => [
                    "message" => "Error del servidor",
                ]
            ], 400);
        }

    }

    public function getAuthUser(Request $request)
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        
    }
}
