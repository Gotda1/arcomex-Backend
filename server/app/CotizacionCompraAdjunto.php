<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionCompraAdjunto extends Model
{
    protected $table = "cotizacion_compra_adjuntos";
    protected $fillable = ["cotizacion_compra_id", "adjunto", "descripcion"];
    public $timestamps = false;
}
