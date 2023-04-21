<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ESProducto extends Model
{
    protected $table = "es_productos";
    protected $fillable = [ 
        "producto_id", 
        "almacen_id", 
        "tipo", 
        "referencia", 
        "observaciones", 
        "cantidad", 
        "piezas", 
        "precio", 
        "existencias_totales", 
        "piezas_totales",
        "precio_totales",
        "existencias_almacen", 
        "piezas_almacen", 
        "precio_almacen", 
        "usuario_registra" 
    ];
    public function setUpdatedAt($value) {  }

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }

    public function almacen(){
        return $this->belongsTo(Almacen::class, "almacen_id", "id");
    }

}