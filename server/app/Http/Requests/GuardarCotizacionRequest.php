<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCotizacionRequest extends ApiRequest
{
   /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() === "POST") {
            return $this->tienePermiso("INSCOT");
        } else {
            return $this->tienePermiso("UPDCOT");
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $reglas = [
            "adquisidor_id"    => "required",
            "forma_pago"       => "max:150",
            "localidad"        => "max:150",
            "catalogo"         => "required",
            "iva"              => "required",
            "observaciones"    => "max:500",
            "tiempo_entrega"   => "max:100",
            "vigencia"         => "max:100",
            "cuerpo"           => "required",
            "adjuntosserv"     => "nullable"
        ];        

        return $reglas;
    }
}
