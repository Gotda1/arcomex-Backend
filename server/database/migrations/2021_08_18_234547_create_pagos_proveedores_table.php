<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosProveedoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pagos_proveedores', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('proveedor_id');
			$table->bigInteger('orden_compra_id');
			$table->bigInteger('orden_compra_pago_id');
			$table->string('referencia', 100)->nullable();
			$table->string('observaciones', 100)->nullable();
			$table->float('importe', 10, 2);
			$table->float('saldo', 10, 2);
			$table->dateTime('fecha_pago');
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
		Schema::drop('pagos_proveedores');
	}

}
