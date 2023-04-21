<?php

namespace App\Http\Requests;

class GuardarProductoRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST")
            return $this->tienePermiso("INSPRD");
        else
            return $this->tienePermiso("UPDPRD");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "clave"                 => "required|max:15|unique:productos,clave," . $this->route("producto"),
            "unidad_id"             => "required",
            "categoria_producto_id" => "required",
            "nombre"                => "required|max:150",
            "descripcion"           => "max:500",
            "color"                 => "nullable",
            "largo"                 => "required",
            "ancho"                 => "required",
            "alto"                  => "required",
            "peso"                  => "required",
            "contenido"             => "required",
            "stock_minimo"          => "required",
            "stock_maximo"          => "required",
            "precio"                => "required",
            "pcompletas"            => "nullable",
            "especial"              => "nullable",
            "status"                => "required",
        ];
    }
}
