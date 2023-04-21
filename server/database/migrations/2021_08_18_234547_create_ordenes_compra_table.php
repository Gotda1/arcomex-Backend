<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenesCompraTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ordenes_compra', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('folio', 15)->unique();
			$table->bigInteger('proveedor_id');
			$table->float('estimado', 10, 0)->default(0); // estimado
			$table->float('subtotal', 10, 0)->default(0); // subtotal
			$table->boolean('iva')->default(0);
			$table->float('total', 10, 0)->default(0); // total
			$table->float('pagado', 10, 0)->default(0); // pagado
			$table->string('forma_pago', 150)->nullable();
			$table->string('observaciones', 500)->nullable();
			$table->boolean('en_almacen');
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
		Schema::drop('ordenes_compra');
	}

}
