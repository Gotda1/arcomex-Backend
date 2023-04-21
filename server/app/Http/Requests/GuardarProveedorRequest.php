<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProveedorRequest extends ApiRequest
{
     /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST")
            return $this->tienePermiso("INSPVD");
        else
            return $this->tienePermiso("UPDPVD");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "clave"     => "required|max:15|unique:proveedores,clave," . $this->route("proveedor"),
            "nombre"    => "required|max:80",
            "rfc"       => "max:20",
            "direccion" => "max:300",
            "email"     => "max:80",
            "telefono"  => "max:15",
            "status"    => "required",
        ];
    }
}
