<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasificacionesAdquisidoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clasificaciones_adquisidores', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('clave', 15)->unique();
			$table->string('nombre', 80);
			$table->string('descripcion', 200);
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
		Schema::drop('clasificaciones_adquisidores');
	}

}
