<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoPagosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pedido_pagos', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('pedido_id');
			$table->string('forma_pago', 150)->nullable();
			$table->float('importe', 10, 2);
			$table->string('observaciones', 100)->nullable();
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
		Schema::drop('pedido_pagos');
	}

}
