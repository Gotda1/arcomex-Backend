<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEsProductosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('es_productos', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('producto_id');
			$table->bigInteger('almacen_id');
			$table->boolean('tipo');
			$table->string('referencia', 100)->nullable();
			$table->float('cantidad', 10, 2);
			$table->float('piezas', 10, 2);
			$table->float('precio', 10, 0)->default(0);
			$table->float('existencias_almacen', 10, 2);
			$table->float('piezas_almacen', 10, 2);
			$table->float('existencias_totales', 10, 2);
			$table->float('piezas_totales', 10, 2);
			$table->float('precio_almacen', 10, 0)->default(0);
			$table->float('precio_totales', 10, 0)->default(0);
			$table->string('observaciones', 500)->nullable();
			$table->bigInteger('usuario_registra');
			$table->dateTime('created_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('es_productos');
	}

}
