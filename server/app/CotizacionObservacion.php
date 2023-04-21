<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionObservacion extends Model
{
    protected $table = "cotizacion_observaciones";
    protected $fillable = [ "cotizacion_id", "observacion", "usuario_registra" ];
    protected $hidden = ["updated_at"];

    public function usuario(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }
}
