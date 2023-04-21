<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoAdjunto extends Model
{
    protected $table = "pedido_adjuntos";
    protected $fillable = ["pedido_id", "adjunto", "descripcion"];
    public $timestamps = false;
}
