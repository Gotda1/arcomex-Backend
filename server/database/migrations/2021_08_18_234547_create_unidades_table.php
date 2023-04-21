<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnidadesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('unidades', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('clave', 10)->unique();
			$table->string('abreviatura', 10);
			$table->string('nombre', 100);
			$table->boolean('status');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('unidades');
	}

}
