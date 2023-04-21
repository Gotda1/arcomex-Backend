<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('usuarios', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('clave', 15)->unique();
			$table->string('rol', 15);
			$table->string('email', 80)->unique();
			$table->string('nombre', 80);
			$table->string('alias', 30);
			$table->string('telefono', 15)->nullable();
			$table->string('password', 100);
			$table->string('token_fcm', 200)->nullable();
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
		Schema::drop('usuarios');
	}

}
