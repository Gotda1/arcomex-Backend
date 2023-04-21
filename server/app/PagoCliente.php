<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagoCliente extends Model
{
    protected $table = "pagos_clientes";

    protected $fillable = [
        "cliente_id", 
        "pedido_id", 
        "pedido_pago_id", 
        "referencia", 
        "importe", 
        "saldo", 
        "observaciones",
        "created_at",
        "updated_at",
        "usuario_registra"
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class, "cliente_id", "id");
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }
}
