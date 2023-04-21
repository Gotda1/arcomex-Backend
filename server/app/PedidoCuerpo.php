<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoCuerpo extends Model
{
    protected $table = "pedido_cuerpo";
    protected $fillable = [
        "pedido_id", 
        "producto_id", 
        "cantidad", 
        "piezas", 
        "cantidad_surt", 
        "piezas_surt", 
        "descripcion", 
        "precio_lista", 
        "descuento", 
        "precio"
    ];
    public $timestamps = false;

    public function producto(){
        return $this->belongsTo(Producto::class, "producto_id", "id");
    }

    public function pedido(){
        return $this->belongsTo(Pedido::class, "pedido_id", "id");
    }
}
