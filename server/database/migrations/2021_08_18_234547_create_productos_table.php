<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('productos', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('unidad_id');
			$table->bigInteger('categoria_producto_id');
			$table->string('clave', 15)->unique();
			$table->string('nombre', 150);
			$table->string('descripcion', 500)->nullable();
			$table->string('color', 50)->nullable();
			$table->float('largo', 10, 2);
			$table->float('ancho', 10, 2);
			$table->float('alto', 10, 2);
			$table->float('peso', 10, 2);
			$table->float('contenido', 10, 2);
			$table->float('stock_minimo', 10, 2);
			$table->float('stock_maximo', 10, 2);
			$table->float('existencias', 10, 0)->default(0);
			$table->float('piezas', 10, 0)->default(0);
			$table->float('existencias_precio', 10, 0)->default(0);
			$table->float('precio', 10, 2);
			$table->boolean('pcompletas')->default(0);
			$table->boolean('especial')->default(0);
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
		Schema::drop('productos');
	}

}
