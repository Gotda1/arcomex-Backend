<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagoProveedor extends Model
{
    protected $table = "pagos_proveedores";

    protected $fillable = [
        "proveedor_id",  
        "orden_compra_id",
        "orden_compra_pago_id",
        "referencia", 
        "observaciones",
        "importe", 
        "saldo", 
        "usuario_registra",
        "fecha_pago",
        "created_at",
        "updated_at"
    ];
}
