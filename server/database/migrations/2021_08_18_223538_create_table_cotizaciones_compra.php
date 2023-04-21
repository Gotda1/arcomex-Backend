<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCotizacionesCompra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("cotizaciones_compra", function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string("folio", 15)->unique();
            $table->bigInteger("proveedor_id");
            $table->string("observaciones", 500)->nullable();
            $table->float("total", 10,2)->default(0);
            $table->boolean("status");
            $table->bigInteger("usuario_registra");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("cotizaciones_compra");
    }
}
