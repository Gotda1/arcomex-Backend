<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Cotizacion extends Model
{
    protected $table = "cotizaciones";
    protected $fillable = [ 
        "adquisidor_id", 
        "vendedor_id", 
        "pedido_id", 
        "folio", 
        "catalogo", 
        "suma", 
        "iva", 
        "total", 
        "observaciones", 
        "forma_pago",
        "localidad",
        "tiempo_entrega",
        "vigencia",
        "precio_puesto",
        "status", 
        "usuario_registra" 
    ];

    
    protected $hidden = ["updated_at"]; 

    public function adquisidor()
    {
        return $this->morphTo("adquisidor", "catalogo");
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, "adquisidor_id", "id");
    }

    public function prospecto(){
        return $this->belongsTo(Prospecto::class, "adquisidor_id", "id");
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }

    public function formaPago(){
        return $this->belongsTo(FormasPago::class, "forma_pago_clave", "clave");
    }

    public function cuerpo(){
        return $this->hasMany(CotizacionCuerpo::class, "cotizacion_id", "id")->orderBy("orden");
    }
    
    public function adjuntosimg(){
        return $this->hasMany(CotizacionAdjunto::class, "cotizacion_id", "id");
    }

    public function observacionesSeg(){
        return $this->hasMany(CotizacionObservacion::class, "cotizacion_id", "id");
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, "pedido_id", "id");
    }
}
