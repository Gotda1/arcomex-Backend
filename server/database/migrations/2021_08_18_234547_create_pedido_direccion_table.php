<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoDireccionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pedido_direccion', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('pedido_id');
			$table->boolean('recoge_almacen');
			$table->string('calle', 100)->nullable();
			$table->string('numero', 50)->nullable();
			$table->string('colonia', 50)->nullable();
			$table->string('localidad', 150)->nullable();
			$table->string('cp', 5)->nullable();
			$table->string('referencia', 150)->nullable();
			$table->string('tipo_obra', 50)->nullable();
			$table->string('nombre_recibe', 150);
			$table->string('telefono', 30)->nullable();
			$table->date('fecha_estimada')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pedido_direccion');
	}

}
