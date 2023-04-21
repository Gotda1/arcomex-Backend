<?php

namespace App\Mail;

use App\DatosEmpresa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EliminaPedidoMailer extends Mailable
{
    var $folio;
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $folio )
    {
        $this->folio = $folio;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datosempresa = DatosEmpresa::first();

        return $this->view('mails.pedidoeliminado')
            ->subject("Pedido eliminado: " . $this->folio)
            ->with("pedido", $this->folio)
            ->with("datosempresa", $datosempresa);
    }
}
