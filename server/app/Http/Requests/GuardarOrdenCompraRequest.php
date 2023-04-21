<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarOrdenCompraRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST") 
            return $this->tienePermiso("INSOCMP");
        else
            return $this->tienePermiso("UPDOCMP");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $reglas = [
            "proveedor_id"   => "required",
            "observaciones"  => "max:500",
            "iva"            => "required",
            "cuerpo"         => "required",
        ];

        if ( !request("en_almacen") ){
            $reglas = array_merge($reglas, [
                "calle"          => "required|max:100",
                "numero"         => "required|max:50",
                "colonia"        => "required|max:50",
                "cp"             => "max:100",
                "referencia"     => "max:100",
                "tipo_obra"      => "max:100",
                "nombre_recibe"  => "required|max:150",
                "telefono"       => "required|max:30"
            ]);
        }

        return $reglas;
    }
}
