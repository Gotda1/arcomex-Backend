<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasarAPedidoRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->tienePermiso("PDOCOT");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $recoge_almacen = request("recoge_almacen");

        $validaciones = [
            "nombre_recibe"          => "required|max:150",
            "telefono"               => "required|max:30",
            "observaciones"          => "max:500",
            "observaciones_internas" => "max:500",
            "fecha_estimada"         => "required",
            "forma_pago"             => "max:150",
            "importe_recibido"       => "required",
            "recoge_almacen"         => "required",
        ];

        $vals_direccion = [
            "calle"                  => "required|max:100",
            "numero"                 => "required|max:50",
            "colonia"                => "required|max:50",
            "localidad"              => "required|max:150",
            "cp"                     => "max:100",
            "referencia"             => "required|max:100",
            "tipo_obra"              => "required|max:100",
        ];

        if(!$recoge_almacen ){
            $validaciones = array_merge($validaciones, $vals_direccion);
        }

        return $validaciones;
    }
}
