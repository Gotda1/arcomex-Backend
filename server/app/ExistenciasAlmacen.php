<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExistenciasAlmacen extends Model
{
    protected $table = "existencias_almacen";
    protected $fillable = [ "producto_id", "almacen_id", "existencias", "piezas", "precio" ];
}
