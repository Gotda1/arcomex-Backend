<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionCompraCuerpo extends Model
{
    protected $table = "cotizaciones_compra_cuerpo";
    protected $fillable = [
        "id", 
        "cotizacion_compra_id", 
        "producto_id", 
        "cantidad", 
        "piezas", 
        "peso", 
        "precio_u", 
        "total", 
        "descripcion", 
        "presupuesto", 
        "color"
    ];  

    public $timestamps = false;

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }
}
 