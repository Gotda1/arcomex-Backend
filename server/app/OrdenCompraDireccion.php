<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraDireccion extends Model
{
    protected $table = "orden_compra_direccion";
    protected $fillable = [ "orden_compra_id", "calle", "numero", "colonia", "cp", "referencia", "tipo_obra", "nombre_recibe", "telefono", "fecha_estimada" ];
    public $timestamps = false;
}
