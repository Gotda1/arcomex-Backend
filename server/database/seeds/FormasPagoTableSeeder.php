<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormasPagoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("formas_pago")->truncate();
        DB::table("formas_pago")->insert([
            [
                "clave" => "EFCT",
                "nombre" => "Efectivo",
            ],[
                "clave" => "DPTO",
                "nombre" => "Deposito",
            ],[
                "clave" => "TRNS",
                "nombre" => "Transferencia",
            ]
        ]);
    }
}
