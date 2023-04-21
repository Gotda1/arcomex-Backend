<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pedidos', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('cliente_id');
			$table->bigInteger('vendedor_id');
			$table->string('folio', 25)->unique();
			$table->float('suma', 10, 0)->default(0);
			$table->boolean('iva');
			$table->float('total', 10, 0)->default(0);
			$table->string('observaciones', 500)->nullable();
			$table->string('observaciones_internas', 500)->nullable();
			$table->string('precio_puesto', 100)->nullable();
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
		Schema::drop('pedidos');
	}

}
