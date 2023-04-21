<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("roles")->truncate();
        DB::table("roles")->insert([[
                "clave" => "ADMN",
                "nombre" => "Administrador",
                "descripcion" => "Administrador",
            ], [
                "clave" => "VTAS",
                "nombre" => "Ventas",
                "descripcion" => "Ventas"
            ], [
                "clave" => "COMP",
                "nombre" => "Compras",
                "descripcion" => "Compras",
            ], [
                "clave" => "ALMC",
                "nombre" => "Almacen",
                "descripcion" => "Almacen",
            ],[
                "clave" => "CONT",
                "nombre" => "Contabilidad",
                "descripcion" => "Contabilidad",
            ]
        ]); 
    }
}

