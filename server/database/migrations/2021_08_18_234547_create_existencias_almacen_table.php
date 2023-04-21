<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExistenciasAlmacenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('existencias_almacen', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('producto_id');
			$table->bigInteger('almacen_id');
			$table->float('existencias', 10, 0)->default(0);
			$table->float('piezas', 10, 0)->default(0);
			$table->float('precio', 10, 0)->default(0);
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
		Schema::drop('existencias_almacen');
	}

}
