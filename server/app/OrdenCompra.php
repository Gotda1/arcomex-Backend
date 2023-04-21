<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = "ordenes_compra";
    protected $fillable = [ 
        "id", 
        "folio", 
        "proveedor_id", 
        "estimado",
        "subtotal",
        "total",
        "pagado",
        "iva",
        "observaciones", 
        "status", 
        "usuario_registra", 
        "en_almacen" 
    ];

    public function cuerpo(){
        return $this->hasMany(OrdenCompraCuerpo::class, "orden_compra_id", "id");
    }

    public function proveedor(){
        return $this->belongsTo(Proveedor::class, "proveedor_id", "id");
    }
    
    public function direccion(){
        return $this->hasOne(OrdenCompraDireccion::class, "orden_compra_id", "id");
    }

    public function uregistra(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }
}
