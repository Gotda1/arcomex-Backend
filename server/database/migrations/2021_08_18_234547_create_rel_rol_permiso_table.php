<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelRolPermisoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rel_rol_permiso', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('rol');
			$table->string('permiso');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rel_rol_permiso');
	}

}
