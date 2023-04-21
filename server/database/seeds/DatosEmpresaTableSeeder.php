<?php

use App\DatosEmpresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("datos_empresa")->truncate();
        DatosEmpresa::create([
            "nombre" => "Canteras Arcomex GDL", 
            "direccion_1" => "La Platza Local 7 Av. López Mateos Sur 7023", 
            "direccion_2" => "Tlajomulco de Zúñiga, JAL.", 
            "telefonos" => "333-161-8314 y 333-161-8314", 
            "web" => "www.arcomex.com", 
            "usuario_registra" => "1"
        ]);
    }
}
