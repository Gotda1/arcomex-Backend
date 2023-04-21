<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCotizacionesCompraCuerpo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("cotizaciones_compra_cuerpo", function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger("cotizacion_compra_id");
            $table->bigInteger("producto_id");
            $table->float("cantidad", 10,2)->default(0);
            $table->float("piezas", 10,2)->default(0);            
            $table->float("peso", 10,2)->default(0);
            $table->float("precio_u", 10,2)->default(0);
            $table->float("total", 10,2)->default(0);
            $table->string("descripcion", 250);
            $table->string("presupuesto", 80);
            $table->string("color", 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("cotizaciones_compra_cuerpo");
    }
}
