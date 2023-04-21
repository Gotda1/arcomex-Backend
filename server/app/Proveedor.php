<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = "proveedores";

    protected $fillable = [
        "clave", 
        "nombre", 
        "rfc", 
        "direccion", 
        "email", 
        "alias", 
        "telefono", 
        "status", 
        "usuario_registra"
    ];

    public function productosprov(){
        return $this->hasMany(ProveedorProducto::class, "proveedor_id", "id");
    }

    /**
     * Obtener saldo por pagar a proveedor
     *
     * @param int $proveedor_id
     * @return float $saldo
     */
    public function obtenerSaldo()
    {
        $ultimo = PagoProveedor::where("proveedor_id", $this->id)
            ->pluck("saldo")
            ->last();

        return $ultimo ? $ultimo : 0;
    }
}
