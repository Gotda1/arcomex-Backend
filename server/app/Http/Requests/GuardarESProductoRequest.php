<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarESProductoRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->tienePermiso("ESPRD");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "producto_id"   => "required",
            "almacen_id"    => "required",
            "tipo"          => "required",
            "cantidad"      => "required",
            "piezas"        => "required",
            "precio"        => "required",
            "referencia"    => "max:150",
            "observaciones" => "max:500",
        ];
    }
}
