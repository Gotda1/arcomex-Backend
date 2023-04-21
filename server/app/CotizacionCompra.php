<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionCompra extends Model
{
    protected $table = "cotizaciones_compra";

    protected $fillable = [ 
        "folio", 
        "proveedor_id", 
        "observaciones", 
        "status", 
        "usuario_registra"
    ];

    public function cuerpo(){
        return $this->hasMany(CotizacionCompraCuerpo::class, "cotizacion_compra_id", "id");
    }

    public function proveedor(){
        return $this->belongsTo(Proveedor::class, "proveedor_id", "id");
    }

    public function uregistra(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }

    public function adjuntosimg(){
        return $this->hasMany(CotizacionCompraAdjunto::class, "cotizacion_compra_id", "id");
    }
}
 