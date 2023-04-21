<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosClientesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pagos_clientes', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('cliente_id');
			$table->bigInteger('pedido_id');
			$table->bigInteger('pedido_pago_id');
			$table->string('referencia', 100)->nullable();
			$table->string('observaciones', 100)->nullable();
			$table->float('importe', 10, 2);
			$table->float('saldo', 10, 2);
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
		Schema::drop('pagos_clientes');
	}

}
