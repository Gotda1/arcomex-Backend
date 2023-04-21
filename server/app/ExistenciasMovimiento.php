<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExistenciasMovimiento extends Model
{
    protected $table = "existencias_movimiento";
    protected $fillable = [ "producto_id", "movimiento_id", "almacen_id", "existencias", "piezas", "precio" ];

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }

    public function almacen(){
        return $this->belongsTo(Almacen::class, "almacen_id", "id");
    }
}
