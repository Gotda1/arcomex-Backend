<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cotizaciones', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('adquisidor_id');
			$table->bigInteger('vendedor_id')->default(0);
			$table->bigInteger('pedido_id')->default(0);
			$table->string('forma_pago', 150)->nullable();
			$table->string('folio', 25)->unique();
			$table->string('catalogo', 10);
			$table->float('suma', 10, 0)->default(0);
			$table->boolean('iva');
			$table->float('total', 10, 0)->default(0);
			$table->string('observaciones', 500)->nullable();
			$table->string('localidad', 150)->nullable();
			$table->string('tiempo_entrega', 100)->nullable();
			$table->string('precio_puesto', 100)->nullable();
			$table->string('vigencia', 100);
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
		Schema::drop('cotizaciones');
	}

}
