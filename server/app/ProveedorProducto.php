<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProveedorProducto extends Model
{
    protected $table = "proveedores_productos";
    protected $fillable = ["producto_id", "proveedor_id", "precio_lista"];
    public $timestamps = false;

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }
}
