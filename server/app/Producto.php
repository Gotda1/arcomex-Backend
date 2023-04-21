<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = "productos";

    protected $fillable = [ 
        "unidad_id", 
        "categoria_producto_id",
        "clave", 
        "nombre", 
        "descripcion",
        "color",
        "contenido", 
        "largo", 
        "ancho", 
        "alto", 
        "peso", 
        "stock_minimo", 
        "stock_maximo",
        "pcompletas",
        "especial",
        "unidades",
        "divisible",
        "existencias", 
        "piezas", 
        "precio",
        "usuario_registra",
        "status" 
    ];

    protected $hidden = [ "usuario_registra", "created_at", "updated_at" ];

    public function unidad(){
        return $this->belongsTo(Unidad::class, "unidad_id", "id");
    }

    public function categoriaProducto(){
        return $this->belongsTo(CategoriaProducto::class, "categoria_producto_id", "id");
    }

    public function proveedores(){
        return $this->belongsToMany(Proveedor::class, "proveedores_productos", "producto_id", "proveedor_id")
            ->withPivot("precio_lista");
    }

    public function existenciasAlmacen(){
        return $this->hasMany(ExistenciasAlmacen::class, "producto_id", "id");
    }

    public function existenciasMov(){
        return $this->hasMany(ExistenciasMovimiento::class, "producto_id", "id");
    }

    public function movimientos(){
        return $this->hasMany(ESProducto::class, "producto_id", "id");
    }

    public function maxPreciosProv(){
        return $this->hasOne(ProveedorProducto::class, "producto_id", "id")
        ->orderBy('precio_lista', 'desc');
    }

    public function enPedidos(){
        return $this->hasMany(PedidoCuerpo::class, "producto_id", "id")
        ->join("pedidos", 'pedidos.id', '=', 'pedido_cuerpo.pedido_id')
        ->where( "cantidad", ">", "cantidad_surt")
        ->where( "pedidos.status", "=", 0);
    }
}

// APMG20