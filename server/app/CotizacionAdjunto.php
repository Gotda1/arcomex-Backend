<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionAdjunto extends Model
{
    protected $table = "cotizacion_adjuntos";
    protected $fillable = ["cotizacion_id", "adjunto", "descripcion"];
    public $timestamps = false;
}
