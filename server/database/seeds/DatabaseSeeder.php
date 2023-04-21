<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->call(DatosEmpresaTableSeeder::class);
            $this->call(UnidadesTableSeeder::class);
            $this->call(PermisosTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(UsuariosTableSeeder::class);
            $this->call(ClientesTableSeeder::class);
            $this->call(AlmacenesTableSeeder::class);
            $this->call(ProveedoresTableSeeder::class);
            $this->call(ProductosTableSeeder::class);
            $this->call(PagosClienteTableSeeder::class);
            
        } catch (\Throwable $th) {
            report($th);
        }
    }
}
