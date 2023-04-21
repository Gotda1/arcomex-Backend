<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Cliente extends Model
{
    protected $hidden  = ["usuario_registra", "created_at", "updated_at"];

    protected $fillable = [
        "clave", 
        "clasificacion_clave", 
        "usuario_id", 
        "nombre", 
        "rfc", 
        "direccion", 
        "email", 
        "alias", 
        "localidad", 
        "telefono", 
        "status", 
        "usuario_registra"
    ];

    public function clasificacion(){
        return $this->belongsTo(ClasificacionAdquisidor::class, "clasificacion_clave", "clave");
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, "usuario_id", "id");
    }

    public function pedidos(){
        return $this->hasMany(Pedido::class,"cliente_id", "id");
    }

    public function ultimosPedidos(){
        return $this->pedidos()
        ->where("status", "!=", "-1")
        ->orderBy("id","desc")
        ->take( 2 );
    }

    public function cotizaciones()
    {
        return $this->morphMany(Cotizacion::class, "adquisidor","catalogo", "adquisidor_id");
    }
}
