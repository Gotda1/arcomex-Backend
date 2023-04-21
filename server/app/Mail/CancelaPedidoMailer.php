<?php

namespace App\Mail;

use App\DatosEmpresa;
use App\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancelaPedidoMailer extends Mailable
{
    use Queueable, SerializesModels;
    
    var $pedido;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $pedido_id )
    {
        $pedido = Pedido::find($pedido_id);
        $this->pedido = $pedido;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datosempresa = DatosEmpresa::first();

        return $this->view('mails.pedidocancelado')
            ->subject("Pedido cancelado: " . $this->pedido->folio)
            ->with("pedido", $this->pedido)
            ->with("datosempresa", $datosempresa);
    }
}
