<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriasProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categorias_productos', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string("nombre");
            $table->string("descripcion");
			$table->boolean('status');
			$table->bigInteger('usuario_registra');
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
        Schema::dropIfExists('categorias_productos');
    }
}
