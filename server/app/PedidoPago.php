<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoPago extends Model
{
    protected $table = "pedido_pagos";

    protected $fillable = ["pedido_id", "forma_pago", "importe", "observaciones", "usuario_registra"];
}
