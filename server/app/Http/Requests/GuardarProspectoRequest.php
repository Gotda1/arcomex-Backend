<?php

namespace App\Http\Requests;


class GuardarProspectoRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST")
            return $this->tienePermiso("INSPRSP");
        else
            return $this->tienePermiso("UPDPRSP");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "clasificacion_clave" => "required",
            "usuario_id"          => "nullable",
            "nombre"              => "required|max:80",
            "localidad"           => "max:50",
            "rfc"                 => "max:20",
            "direccion"           => "max:300",
            "email"               => "required|max:80",
            "telefono"            => "required|max:15",
            "status"              => "required",
        ];
    }
}
