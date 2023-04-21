<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenCompraCuerpoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orden_compra_cuerpo', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('orden_compra_id');
			$table->bigInteger('producto_id');
			$table->float('cantidad', 10, 2);
			$table->string('descripcion', 250);
			$table->float('piezas', 10, 2);
			$table->float('precio_lista', 10, 2);
			$table->float('precio', 10, 2);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orden_compra_cuerpo');
	}

}
