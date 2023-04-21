<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("unidades")->truncate();
        DB::table("unidades")->insert([[
            "clave"       => "M",
            "abreviatura" => "m",
            "nombre"      => "Metros lineales",
            "status"      => 1,
        ], [
            "clave"       => "M2",
            "abreviatura" => "m2",
            "nombre"      => "Metros cuadrados",
            "status"      => 1,
        ],[
            "clave"       => "M2LL",
            "abreviatura" => "m2",
            "nombre"      => "Metros cuadrados LL",
            "status"      => 1,
        ], [
            "clave"       => "M2IR",
            "abreviatura" => "m2",
            "nombre"      => "Metros cuadrados irregulares",
            "status"      => 1,
        ], [
            "clave"       => "L",
            "abreviatura" => "L",
            "nombre"      => "Litros",
            "status"      => 1,
        ], [
            "clave"       => "PZA",
            "abreviatura" => "pza",
            "nombre"      => "piezas",
            "status"      => 1,
        ]]);
    }
}
