<?php

namespace App\Http\Requests;

class GuardarUsuarioRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST")
            return $this->tienePermiso("INSUSR");
        else
            return $this->tienePermiso("UPDUSR");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "rol"      => "required",
            "clave"    => "required|max:15|unique:usuarios,clave," . $this->route("usuario"),
            "nombre"   => "required|max:80",
            "email"    => "required|max:80|unique:usuarios,email," . $this->route("usuario"),
            "alias"    => "required|max:80",
            "telefono" => "max:15",
            "password" => $this->method() === "POST" ? "required|max:12" : "max:12",
            "status"   => "required",
        ];
    }
}
