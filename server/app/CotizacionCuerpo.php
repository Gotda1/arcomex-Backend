<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionCuerpo extends Model
{
    protected $table = "cotizacion_cuerpo";
    protected $fillable = [
        "cotizacion_id", 
        "producto_id", 
        "descripcion", 
        "cantidad", 
        "piezas", 
        "precio", 
        "precio_lista", 
        "descuento",
        "orden"
    ];
    public $timestamps = false;

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }
}
