<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoDireccion extends Model
{
    protected $table = "pedido_direccion";
    protected $fillable = [ 
        "pedido_id",
        "recoge_almacen",
        "calle", 
        "numero", 
        "colonia", 
        "localidad", 
        "cp", 
        "referencia", 
        "tipo_obra", 
        "nombre_recibe", 
        "telefono", 
        "fecha_estimada" 
    ];
    public $timestamps = false;
}
