<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProspectosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('prospectos', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('usuario_id');
			$table->string('clasificacion_clave', 15);
			$table->string('rfc', 20)->nullable();
			$table->string('direccion', 300)->nullable();
			$table->string('localidad', 50)->nullable();
			$table->string('email', 80);
			$table->string('nombre', 80);
			$table->string('telefono', 15);
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
		Schema::drop('prospectos');
	}

}
