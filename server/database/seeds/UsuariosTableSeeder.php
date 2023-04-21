<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuariosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("usuarios")->truncate();

        DB::table("usuarios")->insert([[
            "clave"            => "001",
            "rol"              => "ADMN",
            "email"            => "guadalupe.ulloa@outlook.com",
            "nombre"           => "Guadalupe Ulloa",
            "alias"            => "gulloa",
            "telefono"         => "3320629615",
            "password"         => bcrypt("123456"),
            "token_fcm"        => "",
            "status"           => 1,
            "usuario_registra" => 0
        ], [
            "clave"            => "AM",
            "rol"              => "VTAS",
            "email"            => "gteventas@canterasarcomexgdl.com",
            "nombre"           => "Araceli M",
            "alias"            => "aracelim",
            "telefono"         => "",
            "password"         => bcrypt("123456"),
            "token_fcm"        => "",
            "status"           => 1,
            "usuario_registra" => 0
        ], [
            "clave"            => "CH",
            "rol"              => "VTAS",
            "email"            => "ventas6@canterasarcomexgdl.com",
            "nombre"           => "Celina H",
            "alias"            => "celinah",
            "telefono"         => "",
            "password"         => bcrypt("123456"),
            "token_fcm"        => "",
            "status"           => 1,
            "usuario_registra" => 0
        ], [
            "clave"            => "EA",
            "rol"              => "COMP",
            "email"            => "compras@canterasarcomexgdl.com",
            "nombre"           => "Eduardo Arcomex",
            "alias"            => "eduardo",
            "telefono"         => "",
            "password"         => bcrypt("123456"),
            "token_fcm"        => "",
            "status"           => 1,
            "usuario_registra" => 0
        ]]);
    }
}
