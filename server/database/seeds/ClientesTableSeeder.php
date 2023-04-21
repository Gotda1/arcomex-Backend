<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("clasificaciones_adquisidores")->truncate();
        DB::table("clasificaciones_adquisidores")->insert([
            [
                "clave"            => "C",
                "nombre"           => "Constructora",
                "descripcion"      => "Constructora",
                "usuario_registra" => "1"
            ],[
                "clave"            => "CI",
                "nombre"           => "Constructor independiente",
                "descripcion"      => "Constructor independiente",
                "usuario_registra" => "1"
            ],[
                "clave"            => "D",
                "nombre"           => "Distribuidores",
                "descripcion"      => "Distribuidores",
                "usuario_registra" => "1"
            ],[
                "clave"            => "PG",
                "nombre"           => "Público en general",
                "descripcion"      => "Público en general",
                "usuario_registra" => "1"
            ]
        ]);
    }
}
