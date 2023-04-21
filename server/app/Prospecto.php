<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = "prospectos";

    protected $hidden  = ["usuario_registra", "created_at", "updated_at"];

    protected $fillable = [
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

    public function cotizaciones()
    {
        return $this->morphMany(Cotizacion::class, "adquisidor","catalogo", "adquisidor_id");
    }
}
