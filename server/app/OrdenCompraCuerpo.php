<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraCuerpo extends Model
{
    protected $table = "orden_compra_cuerpo";
    protected $fillable = [
        "id", 
        "orden_compra_id", 
        "producto_id", 
        "cantidad", 
        "descripcion", 
        "piezas", 
        "precio", 
        "precio_lista"
    ];
    
    public $timestamps = false;

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }
}
