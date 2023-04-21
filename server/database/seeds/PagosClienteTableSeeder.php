<?php

use App\PagoCliente;
use App\PagoProveedor;
use App\Pedido;
use App\PedidoPago;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagosClienteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {            
            $pagos_pedidos = PedidoPago::orderBy("id", "asc")
                                ->get()
                                ->toArray();

            
            DB::table("pagos_clientes")->truncate();

            foreach ($pagos_pedidos as $pago) {

                $pedido = Pedido::find( $pago["pedido_id"] );
                
                
                
                $saldo  = $this->obtenerSaldo( $pedido["cliente_id"] );
                $nvoSaldo = ( $pedido->total + $saldo ) - $pago["importe"];

                // Log::info("SALDO", [$saldo, $pago->importe]);

                PagoCliente::create([
                    "cliente_id"       => $pedido->cliente_id, 
                    "referencia"       => $pedido->folio, 
                    "importe"          => $pago["importe"], 
                    "saldo"            => $nvoSaldo, 
                    "usuario_registra" => 0
                    ]);

                    Log::info([$saldo]);
            }
        } catch (\Throwable $th) {
            report($th);
        }
    }

    /**
     * Obtener saldo por cobrar a cliente
     *
     * @param int $cliente_id
     * @return float $saldo
     */
    private function obtenerSaldo( $cliente_id ){
        $ultimo = PagoCliente::where("cliente_id", $cliente_id)
            ->pluck("saldo")
            ->last();

        return $ultimo ? $ultimo : 0;
    }
}
