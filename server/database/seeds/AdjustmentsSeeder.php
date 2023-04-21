<?php

use App\Cliente;
use App\Cotizacion;
use App\Pedido;
use App\Prospecto;
use App\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $pedidos = Pedido::with("cliente.usuario")->get();

        // foreach ( $pedidos as $pedido ) {
        //     $clave = $pedido->cliente->usuario->clave;
        //     Pedido::where("id", $pedido->id)->update([
        //         "folio" => $clave . "-" . $pedido->folio
        //     ]);            
        // }


        
        $cotizaciones = Cotizacion::all();

        foreach ( $cotizaciones as $cotizacion ) {
            $adquisidor = ( $cotizacion->catalogo == "clientes" ) ? 
                        Cliente::with("usuario")->find( $cotizacion->adquisidor_id ) :
                        Prospecto::with("usuario")->find( $cotizacion->adquisidor_id );

            //Log::info($adquisidor);
            
            $usuario = $adquisidor ? $adquisidor->usuario : Usuario::find( $cotizacion->usuario_registra );
            
            $pre     = $usuario->clave;
            Log::info($pre);

            // $folio  = $pre . "-" . str_pad($ultimo, 7, "0", STR_PAD_LEFT);

            Cotizacion::where("id", $cotizacion->id)->update([
                "vendedor_id" => $usuario->id,
                "folio"       => $pre . "-" . $cotizacion->folio,
            ]);            
        }
    }
}
