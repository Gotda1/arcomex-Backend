<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\PagoCliente;
use App\Producto;
use App\RelRolPermiso;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    public function __construct()
    {
    }
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function tienePermiso( $permiso )
    {
        #   rol requester
        $claveRol = request("usuarioDB")["rol"]["clave"];

        return RelRolPermiso::where("rol", $claveRol)
            ->where("permiso", $permiso)
            ->first();
    }

    public function calcularUnidades( $producto_id, $cantidad ){
        $producto = Producto::find( $producto_id );
        Log::info([$producto->clave, $producto->unidad_id]);
        switch ( $producto->unidad_id ) {
            // Metros lineales
            case 1:
                return $cantidad / $producto->largo;
                break;
            // Metros cuadrados
            case 2:
                return $cantidad / ( $producto->largo * $producto->ancho );
                break;
            // Piezas
            case 3:
                return $cantidad;
                break;
            
            default:
                return $cantidad;
                break;
        }
    }

    public function calculaExistencias($producto_id, $cantidad, $tipo){
        $producto = Producto::find( $producto_id );

        if ( $tipo === "e")
            $existencias = $producto->existencias + $cantidad;
        else
            $existencias = $producto->existencias - $cantidad;
        
        $unidades = $this->calcularUnidades( $producto_id, $existencias );
        
        return [
            "e" => $existencias, 
            "u"    => $unidades 
        ];
    }

    /**
     * Arma clave de cliente de acuerdo a su clasificación
     *
     * @param string $clasificacion del cliente
     * @return string clave generada
     */
    public function armaClaveCliente($clasificacion){
        $ultimo = Cliente::where("clasificacion_clave", $clasificacion)
                        ->orderBy("clave", "desc")
                        ->pluck("clave")
                        ->first();

        if (!$ultimo){
            return $clasificacion . "-00001";
        }  else { 
            $ultimo = explode( "-", $ultimo )[1] + 1;
            return $clasificacion . "-" . str_pad($ultimo, 5, "0", STR_PAD_LEFT);
        }   

    }


    public function almacenaImagenB64( $carpeta, $folio, $base64)
    {
        if( !$base64 ) return "";

        $nombre = uniqid() . ".png";
        $path = "$carpeta/" . $folio . "/" . $nombre;
        $base64 = explode(',', $base64)[1];
        Storage::put($path, base64_decode($base64));
        return $nombre;           
    }

    public function sumaIVA( $monto ){
        $iva = Config::get("app.IVA");
        $total = $monto + ( $monto / 100 * $iva );
        return round($total, 2);
    }

    public function calculaIVA( $monto ){
        $iva = Config::get("app.IVA");
        $iva =  ($monto / 100) * $iva;
        return round($iva, 2);
    }

    function restaIVA( $monto ){
        $iva = Config::get("app.IVA");
        $divIVa = 1 + ($iva / 100);
        return round($monto / $divIVa, 2);
    }

    /**
     * Obtener saldo por cobrar a cliente
     *
     * @param int $cliente_id
     * @return float $saldo
     */
    public function obtenerSaldo( $cliente_id ){
        $ultimo = PagoCliente::where("cliente_id", $cliente_id)
            ->pluck("saldo")
            ->last();

        return $ultimo ? $ultimo : 0;
    }
}
