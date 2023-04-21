<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenCompraDireccionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orden_compra_direccion', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('orden_compra_id');
			$table->string('calle', 100);
			$table->string('numero', 50);
			$table->string('colonia', 50);
			$table->string('localidad', 150)->nullable();
			$table->string('cp', 5)->nullable();
			$table->string('referencia', 150)->nullable();
			$table->string('tipo_obra', 50)->nullable();
			$table->string('nombre_recibe', 150);
			$table->string('telefono', 30);
			$table->date('fecha_estimada');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orden_compra_direccion');
	}

}
