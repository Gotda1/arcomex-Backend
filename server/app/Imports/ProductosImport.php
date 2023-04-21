<?php

namespace App\Imports;

use App\CategoriaProducto;
use App\Producto;
use App\Unidad;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductosImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;
    var $categorias = [];
    var $unidades   = [];

    public function __construct()
    {
        $this->categorias = CategoriaProducto::where("status", 1)
                                    ->get();
        $this->unidades = Unidad::where("status", 1)
                                ->get();
    }


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $unidad = $this->unidades->search(function ($item) use($row) {
            return $item->abreviatura == $row["unidad"];
        });
        $categoria= $this->categorias->search(function ($item) use($row) {
            return $item->nombre == $row["categoria"];
        });

        return new Producto([
            "unidad_id"             => $this->unidades[$unidad ]->id,
            "categoria_producto_id" => $this->categorias[$categoria ]->id,
            "clave"                 => $row["clave"],
            "nombre"                => $row["nombre"],
            "descripcion"           => $row["descripcion"],
            "color"                 => $row["color"],
            "contenido"             => $row["contenido"],
            "largo"                 => $row["largo"],
            "ancho"                 => $row["ancho"],
            "alto"                  => $row["alto"],
            "peso"                  => $row["peso"],
            "stock_minimo"          => $row["stock_minimo"],
            "stock_maximo"          => $row["stock_maximo"],
            "pcompletas"            => $row["piezas_completas"],
            "especial"              => $row["especial"],
            "existencias"           => 0,
            "piezas"                => 0,
            "existencias_precio"    => 0,
            "precio"                => $row["precio"],
            "usuario_registra"      => request("usuarioDB")["id"],
            "status"                => $row["status"]
        ]);
    }

    public function rules(): array
    {
        $unidades   = $this->unidades->pluck("abreviatura")->toArray();
        $categorias = $this->categorias->pluck("nombre")->toArray();
        
        return [
            'clave'        => "required|max:15|unique:productos,clave",
            "unidad"       => [
                                "required",
                                function($attribute, $value, $onFailure) use($unidades){
                                    if(!in_array($value, $unidades)){
                                        $onFailure("La unidad $value no existe");
                                    }
                                },
                            ],
            "categoria"    => [
                                    "required",
                                    function($attribute, $value, $onFailure) use($categorias){
                                        if(!in_array($value, $categorias)){
                                            $onFailure("La categoría $value no existe");
                                        }
                                    },
                                ],
            "nombre"       => "required|max:150",
            "descripcion"  => "max:500",
            "color"        => "nullable",
            "largo"        => "required",
            "ancho"        => "required",
            "alto"         => "required",
            "peso"         => "required",
            "contenido"    => "required",
            "stock_minimo" => "required",
            "stock_maximo" => "required",
            "precio"       => "required",
            "pcompletas"   => "nullable",
            "especial"     => "nullable",
            "status"       => "required",
        ];
    }
}
