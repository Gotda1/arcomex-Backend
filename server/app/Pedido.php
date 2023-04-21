<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = "pedidos";
    protected $fillable = [ 
        "folio", 
        "cliente_id", 
        "vendedor_id", 
        "suma", 
        "iva", 
        "total", 
        "observaciones", 
        "observaciones_internas", 
        "status", 
        "usuario_registra" 
    ];
    protected $hidden = ["updated_at"];

    public function cliente(){
        return $this->belongsTo(Cliente::class, "cliente_id", "id");
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, "usuario_registra", "id");
    }

    public function cuerpo(){
        return $this->hasMany(PedidoCuerpo::class, "pedido_id", "id")->orderBy("orden");
    }

    public function surtiendo(){
        return $this->cuerpo()->sum("cantidad_surt");
    }
    
    public function direccion(){
        return $this->hasOne(PedidoDireccion::class, "pedido_id", "id");
    }

    public function pagos(){
        return $this->hasMany(PedidoPago::class, "pedido_id", "id");
    }

    public function adjuntosimg(){
        return $this->hasMany(PedidoAdjunto::class, "pedido_id", "id");
    }
    
    public function pagado(){
        return $this->pagos()->sum("importe");
    }    
}
