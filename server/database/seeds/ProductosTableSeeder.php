<?php

use App\Almacen;
use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\Producto;
use App\Proveedor;
use App\ProveedorProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->productos2021();
        $this->preciosprov();
        $this->existenciasAlm();
    }

    private function productos2021()
    {
        try {      
            DB:: table("productos")->truncate();
            DB:: table("existencias_almacen")->truncate();
    
            // LAMINADOS
            $path = storage_path() . "/productos04012021.json";
            $json = json_decode(file_get_contents($path), true);
    
            foreach ($json as $key => $value) {
                       $unidad_id = 0;                

                $value["UNIDAD"]  = isset($value["UNIDAD"]) ? $value["UNIDAD"] : "PZA";
                switch ($value["UNIDAD"]) {
                    case "ML": 
                        $unidad_id = 1;
                        break;
                    case "MT2": 
                        $unidad_id = 2;
                        break;
                    case "MT2LL": 
                        $unidad_id = 3;
                        break;
                    case "MT2IR": 
                        $unidad_id = 4;
                        break;
                    case "LT": 
                        $unidad_id = 6;
                        break;
                    case "PZA": 
                        $unidad_id = 6;
                        break;
                }
    
                DB::table("productos")->insert([
                    [
                        "unidad_id"        => $unidad_id,
                        "clave"            => $value["NUEVO CODIGO"],
                        "nombre"           => $value["NOMBRE"],
                        "descripcion"      => $value["NOMBRE"],
                        "largo"            => isset($value["LARGO"]) ? $value["LARGO"] / 100 : 0,
                        "ancho"            => isset($value["ANCHO"]) ? $value["ANCHO"] / 100 : 0,
                        "alto"             => 1,
                        "stock_minimo"     => ( !isset($value["STOCK MIN"]) || ( $value["STOCK MIN"] == "S/E" || $value["STOCK MIN"] == "S/P" ) ) ? 10 : $value["STOCK MIN"],
                        "stock_maximo"     => ( !isset($value["STOCK MAX"]) || ( $value["STOCK MAX"] == "S/E" || $value["STOCK MIN"] == "S/P" ) ) ? 1000 : $value["STOCK MAX"],
                        "existencias"      => 0,
                        "piezas"           => 0,
                        "precio"           => isset($value["PRECIO"]) ? (float) $value["PRECIO"] : 0,
                        "peso"             => 0,
                        "contenido"        => (float) $value["CONTENIDO"],
                        "pcompletas"       => (int) $value["PCOMPLETAS"],
                        "especial"         => (int) $value["ESPECIAL"],
                        "usuario_registra" => 1,
                        "status"           => 1,
                    ],
                ]);

                Log::info($value["NUEVO CODIGO"], ["OK"]);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    private function productos2()
    {
        DB:: table("productos")->truncate();
        DB:: table("existencias_almacen")->truncate();

        // LAMINADOS
        $path = storage_path() . "/prodsgral.json";
        $json = json_decode(file_get_contents($path), true);

        foreach ($json as $key => $value) {
                   $unidad_id = 0;
            $value["UNIDAD"]  = isset($value["UNIDAD"]) ? $value["UNIDAD"] : "PZA";
            switch ($value["UNIDAD"]) {
                case "PZA": 
                    $unidad_id = 3;
                    break;
                case "MT2": 
                    $unidad_id = 2;
                    break;
                case "LT": 
                    $unidad_id = 3;
                    break;
            }
            DB::table("productos")->insert([
                [
                    "unidad_id"        => $unidad_id,
                    "clave"            => $value["NUEVO CODIGO"],
                    "nombre"           => $value["NOMBRE"],
                    "descripcion"      => $value["NOMBRE"],
                    "largo"            => isset($value["LARGO"]) ? $value["LARGO"] / 100 : 1,
                    "ancho"            => isset($value["ANCHO"]) ? $value["ANCHO"] / 100 : 1,
                    "alto"             => 1,
                    "stock_minimo"     => $value["STOCK MIN"] == "S/E" || $value["STOCK MIN"] == "S/P" ? 10 : $value["STOCK MIN"],
                    "stock_maximo"     => $value["STOCK MAX"] == "S/E" || $value["STOCK MIN"] == "S/P" ? 1000 : $value["STOCK MAX"],
                    "existencias"      => 0,
                    "piezas"           => 0,
                    "precio"           => $value["PRECIO"],
                    "peso"             => 0,
                    "pcompletas"       => 1,
                    "usuario_registra" => 1,
                    "status"           => 1,
                ],
            ]);
        }
    }

    private function productos()
    {
        DB:: table("productos")->truncate();

        // LAMINADOS
        $path = storage_path() . "/laminados.json";
        $json = json_decode(file_get_contents($path), true);
        foreach ($json as $key => $value) {
            $amedidas = explode("X", $value["MEDIDA CM"]);
            DB::table("productos")->insert([[
                "unidad_id"        => 2,
                "clave"            => $value["CÓDIGO"],
                "nombre"           => $value["NOMBRE"],
                "descripcion"      => $value["CÓDIGO"] . " " . $value["NOMBRE"] . " " . $value["MEDIDA CM"] . " " . $value["PESO KG"],
                "largo"            => $amedidas[0] / 100,
                "ancho"            => $amedidas[1] / 100,
                "stock_minimo"     => $amedidas[0] * $amedidas[1] * 30,
                "stock_maximo"     => $amedidas[0] * $amedidas[1] * 1000,
                "existencias"      => 0,
                "piezas"           => 0,
                "precio"           => $value["PRECIO X M2"],
                "usuario_registra" => 1,
                "status"           => 1
            ]]);
        }

        // FACHALETAS
        $path = storage_path() . "/fachaletas.json";
        $json = json_decode(file_get_contents($path), true);
        foreach ($json as $key => $value) {
            $amedidas = explode("X", $value["MEDIDA CM"]);
            DB::table("productos")->insert([[
                "unidad_id"        => 2,
                "clave"            => $value["CÓDIGO"],
                "nombre"           => $value["NOMBRE"],
                "descripcion"      => $value["CÓDIGO"] . " " . $value["NOMBRE"] . " " . $value["MEDIDA CM"] . " " . $value["PESO"],
                "largo"            => $amedidas[0] / 100,
                "ancho"            => $amedidas[1] / 100,
                "stock_minimo"     => $amedidas[0] * $amedidas[1] * 30,
                "stock_maximo"     => $amedidas[0] * $amedidas[1] * 1000,
                "existencias"      => 0,
                "piezas"           => 0,
                "precio"           => $value["PRECIO X M2"],
                "usuario_registra" => 1,
                "status"           => 1
            ]]);
        }

        // ADHESIVOS
        $path = storage_path() . "/adhesivos.json";
        $json = json_decode(file_get_contents($path), true);
        foreach ($json as $key => $value) {
            DB::table("productos")->insert([[
                "unidad_id"        => 3,
                "clave"            => $value["CÓDIGO"],
                "nombre"           => $value["NOMBRE"],
                "descripcion"      => $value["CÓDIGO"] . " " . $value["NOMBRE"] . " " . $value["COLOR"] . " " . $value["PESO"] . " " . $value["RENDIMIENTO"],
                "largo"            => 0,
                "ancho"            => 0,
                "stock_minimo"     => 30,
                "stock_maximo"     => 1000,
                "existencias"      => 0,
                "piezas"           => 0,
                "precio"           => $value["PRECIO X SACO"],
                "usuario_registra" => 1,
                "status"           => 1
            ]]);
        }

        // SELLADORES
        $path = storage_path() . "/selladores.json";
        $json = json_decode(file_get_contents($path), true);
        foreach ($json as $key => $value) {
            DB::table("productos")->insert([[
                "unidad_id"        => 3,
                "clave"            => $value["CÓDIGO"],
                "nombre"           => $value["NOMBRE"],
                "descripcion"      => $value["CÓDIGO"] . " " . $value["NOMBRE"],
                "largo"            => 0,
                "ancho"            => 0,
                "stock_minimo"     => 30,
                "stock_maximo"     => 1000,
                "existencias"      => 0,
                "piezas"           => 0,
                "precio"           => $value["PRECIO X PZ"],
                "usuario_registra" => 1,
                "status"           => 1
            ]]);
        }
    }


    private function existenciasAlm()
    {
        ESProducto::truncate();
        ExistenciasMovimiento::truncate();
        ExistenciasAlmacen::truncate();
        Producto::where("status", 1)->update([
            "existencias"        => 0,
            "piezas"             => 0,
            "existencias_precio" => 0,
        ]);

        $this->existencias(1, "/existenciasbodega20201213.json");
        $this->existencias(3, "/almacen3_20201214.json");
        $this->existencias(4, "/almacen4_20201214.json");        
    }
    private function existencias($almacen_id, $file)
    {
        try {
            Log:: info($file, [$almacen_id]);
            $path = storage_path() . $file;
            $json = json_decode(file_get_contents($path), true);


            foreach ($json as $key => $value) {
                #   Producto de la bd con existencias en almacén
                $producto = Producto::with(["existenciasAlmacen"  => function ($query) use ($almacen_id) {
                    $query->where("almacen_id", $almacen_id);
                }])->where("nombre", $value["producto"])->get()->first();

                Log:: info($value["clave"], [$producto]);
                if (!$producto) continue;

                #   Existencias actuales totales
                $existencias_totales = $producto->existencias;
                $piezas_totales      = $producto->piezas;
                $precio_totales      = $producto->existencias_precio;
                #   Existencias actuales almacén
                $exsalmacen          = $producto->existenciasAlmacen;
                $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
                $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
                $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;



                $piezas = $this->calcularPiezas($producto->id, $value["existencias"], "E");

                #   Existencias totales
                $existencias_totales += $value["existencias"];
                $piezas_totales      += $piezas;
                $precio_totales      += 0;
                #   Existencias almacén
                $existencias_almacen += $value["existencias"];
                $piezas_almacen      += $piezas;
                $precio_almacen      += 0;


                $movimiento = ESProducto::create([
                    "producto_id"         => $producto->id,
                    "almacen_id"          => $almacen_id,
                    "tipo"                => 1,
                    "cantidad"            => $value["existencias"],
                    "piezas"              => $this->calcularPiezas($producto->id, $value["existencias"]),
                    "precio"              => 0,
                    "referencia"          => "Entrada inicial",
                    "observaciones"       => "Entrada inicial",
                    "piezas_totales"      => $piezas_totales,
                    "existencias_totales" => $existencias_totales,
                    "piezas_totales"      => $piezas_totales,
                    "precio_totales"      => $precio_totales,
                    "piezas_almacen"      => $piezas_almacen,
                    "existencias_almacen" => $existencias_almacen,
                    "precio_almacen"      => $precio_almacen,
                    "precio"              => 0,
                    "usuario_registra"    => 1
                ]);

                #   Actualiza existencias totales
                $produpd = Producto::where("id", $producto->id)->update([
                    "existencias"        => $existencias_totales,
                    "piezas"             => $piezas_totales,
                    "existencias_precio" => $precio_totales
                ]);



                #   Actualiza o inserta existencias almacén
                $almacen = ExistenciasAlmacen::updateOrCreate([
                    "producto_id" => $producto->producto_id,
                    "almacen_id"  => $almacen_id,
                ], [
                    "producto_id" => $producto->id,
                    "almacen_id"  => $almacen_id,
                    "piezas"      => $piezas_almacen,
                    "existencias" => $existencias_almacen,
                    "precio"      => $precio_almacen
                ]);

                #   Inserta existencias en almacenes
                $almacenes = Almacen::all();
                foreach ($almacenes as $almacen) {
                    $existenciasalm = ExistenciasAlmacen::where("almacen_id", $almacen->id)
                        ->where("producto_id", $producto->id)
                        ->get()
                        ->first();
                    $ealm = ExistenciasMovimiento::create([
                        "producto_id"   => $producto->id,
                        "almacen_id"    => $almacen->id,
                        "movimiento_id" => $movimiento->id,
                        "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                        "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                        "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                    ]);
                }
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    private function preciosprov()
    {
        try {

            $path = storage_path() . "/preciosprov.json";
            $json = json_decode(file_get_contents($path), true);

            ProveedorProducto:: truncate();
            foreach ($json as $key => $value) {
                if (!isset($value["CODIGO"])) continue;

                $producto = Producto:: where("clave", $value["CODIGO"])->get();
                          Log::info("producto", [$producto]);

                if (sizeof($producto) == 0) continue;

                if (isset($value["PROV1"])) {

                    Log:: info("proveedor1", [$value["PROV1"]]);

                    $proveedor1 = Proveedor::where("clave", $value["PROV1"])->get();

                    ProveedorProducto::create([
                        "producto_id"  => $producto[0]->id,
                        "proveedor_id" => $proveedor1[0]->id,
                        "precio_lista" => $value["PRECIO1"],
                    ]);
                }

                if (isset($value["PROV2"])) {
                    Log:: info("proveedor2", [$value["PROV2"]]);

                    $proveedor2 = Proveedor::where("clave", $value["PROV2"])->get();
                                ProveedorProducto:: create([
                        "producto_id"  => $producto[0]->id,
                        "proveedor_id" => $proveedor2[0]->id,
                        "precio_lista" => $value["PRECIO2"],
                    ]);
                }

                if (isset($value["PROV3"])) {
                                Log::info("proveedor3", [$value["PROV3"]]);
                    $proveedor3 = Proveedor::where("clave", $value["PROV3"])->get();

                    ProveedorProducto::create([
                        "producto_id"  => $producto[0]->id,
                        "proveedor_id" => $proveedor3[0]->id,
                        "precio_lista" => $value["PRECIO3"],
                    ]);
                }

                if (isset($value["PROV4"])) {
                                Log::info("proveedor4", [$value["PROV4"]]);
                    $proveedor4 = Proveedor:: where("clave", $value["PROV4"])->get();

                    ProveedorProducto::create([
                        "producto_id"  => $producto[0]->id,
                        "proveedor_id" => $proveedor4[0]->id,
                        "precio_lista" => $value["PRECIO4"],
                    ]);
                }
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    public function calcularPiezas($producto_id, $cantidad)
    {
        $producto = Producto::find($producto_id);
        switch ($producto->unidad_id) {
            case 1: // metros lineales
                return round($cantidad / $producto->largo, 2);
                break;
            case 2: // metros cuadrados
                return round($cantidad / ($producto->largo * $producto->ancho), 2);
                break;
            default: 
                return $cantidad;
                break;
        }
    }

    public function calculaExistencias($producto_id, $cantidad, $tipo)
    {
        $producto = Producto::find($producto_id);

        if ($tipo === "e")
            $existencias = $producto->existencias + $cantidad;
        else
            $existencias = $producto->existencias - $cantidad;

        $unidades = $this->calcularPiezas($producto_id, $existencias);

        return [
            "e" => $existencias,
            "u" => $unidades
        ];
    }
}
