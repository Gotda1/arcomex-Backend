<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmacenesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('almacenes', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('clave', 15)->unique();
			$table->string('nombre', 80);
			$table->boolean("status");
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('almacenes');
	}

}
