<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlmacenesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("almacenes")->truncate();
        DB::table("almacenes")->insert([
            [
                "clave" => "ALM1",
                "nombre" => "Bodega",
            ],[
                "clave" => "ALM2",
                "nombre" => "Platza",
            ],[
                "clave" => "ALM3",
                "nombre" => "Saldos",
            ],[
                "clave" => "ALM4",
                "nombre" => "San Agustín",
            ],[
                "clave" => "ALM5",
                "nombre" => "Placas",
            ]
        ]);
    }
}
