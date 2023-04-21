<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("proveedores")->truncate();

        // LAMINADOS
        $path = storage_path() . "/proveedores.json";
        $json = json_decode(file_get_contents($path), true);

        foreach ($json as $key => $value) { 
            DB::table("proveedores")->insert([ 
                "clave"            => trim($value["# PROVEEDOR"]),
                "rfc"              => "",
                "direccion"        => "",
                "email"            => "",
                "nombre"           => trim($value["PROVEEDOR"]),
                "telefono"         => isset($value["TELEFONO"]) ? trim($value["TELEFONO"]) : "",
                "status"           => 1,
                "usuario_registra" => 1
            ]);
        }
    }
}
