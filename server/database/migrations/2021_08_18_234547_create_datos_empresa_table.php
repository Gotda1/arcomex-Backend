<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosEmpresaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('datos_empresa', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('nombre', 500);
			$table->string('direccion_1', 500);
			$table->string('direccion_2', 500)->nullable();
			$table->string('telefonos', 500)->nullable();
			$table->string('web', 500)->nullable();
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
		Schema::drop('datos_empresa');
	}

}
