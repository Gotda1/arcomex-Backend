<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCotizacionCompraRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->tienePermiso("CCMPS");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "proveedor_id"   => "required",
            "observaciones"  => "max:500",
            "cuerpo"         => "required",
        ];
    }
}
