<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraPago extends Model
{
    protected $table = "orden_compra_pagos";

    protected $fillable = [
        "orden_compra_id", 
        "forma_pago", 
        "importe", 
        "usuario_registra"
    ];

}
