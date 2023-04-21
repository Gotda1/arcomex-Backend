<?php

namespace App\Http\Requests;

use App\RelRolPermiso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {   
        $errors = "";
        foreach ($validator->errors()->toArray() as $idx => $v) 
            $errors .= ( $v[0] . " " );

        $response = [
            "head" => "error",
            "body" => [
                "message" => $errors
            ]
        ];

        throw new HttpResponseException(response()->json($response,400));
    }

    public function tienePermiso($permiso)
    {
        $usuario = request("usuarioDB");
        $permisorol = RelRolPermiso::where("rol", $usuario["rol"]["clave"])
            ->where("permiso", $permiso)
            ->first();

        return $permisorol;
    }
}
