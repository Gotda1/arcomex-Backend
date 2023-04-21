<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionCompraAdjuntos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_compra_adjuntos', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
			$table->bigInteger('cotizacion_compra_id');
			$table->string('adjunto', 80);
			$table->string('descripcion', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cotizacion_compra_adjuntos');
    }
}
