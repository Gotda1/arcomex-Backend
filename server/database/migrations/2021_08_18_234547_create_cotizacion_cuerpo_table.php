<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionCuerpoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cotizacion_cuerpo', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('cotizacion_id');
			$table->bigInteger('producto_id');
			$table->float('cantidad', 10, 2);
			$table->string('descripcion', 250);
			$table->float('piezas', 10, 2);
			$table->float('precio_lista', 10, 2);
			$table->float('descuento', 10, 0)->default(0);
			$table->float('precio', 10, 2);
			$table->integer('orden')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cotizacion_cuerpo');
	}

}
