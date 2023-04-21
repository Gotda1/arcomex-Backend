<?php

use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\Producto;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
});

//Route::get("liquidar", "OrdenCompraController@liquidar");

Route::get("/test", "BackupController@test");
// Route::get("/prueba", "PedidosController@prueba");
// Route::get("/prueba2", "OrdenCompraController@prueba");

Route::get("/test2", function(){
    try {
        
        // LAMINADOS
       $path = storage_path() . "/inventario_placas.xlsx";
   
       $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
       $spreadsheet = $reader->load($path);
       $worksheet = $spreadsheet->getActiveSheet();

      

       foreach ($worksheet->toArray() as $idx => $row) {
          if( $idx == 0 || $idx == 1 ) continue;
           #   Unidad M2
           $unidad_id = 2;
           #   Almacén Bodega
           $almacen_id = 1;
           #   Medidas
           $medidasm2 = $row[6] * $row[7];
           #    Precio
           $precio = str_replace( "$", "", $row[11] );
           $precio = (float) str_replace( ",", "", $precio );
   
           #   Producto
           $producto = Producto::create([
                "unidad_id"        => $unidad_id,
                "clave"            => $row[0],
                "nombre"           => $row[4],
                "descripcion"      => $row[5],
                "largo"            => $row[6],
                "ancho"            => $row[7],
                "alto"             => 1,
                "stock_minimo"     => $medidasm2,
                "stock_maximo"     => $medidasm2,
                "existencias"      => $medidasm2,
                "piezas"           => 1,
                "precio"           => $precio,
                "peso"             => 0,
                "contenido"        => 0,
                "pcompletas"       => 1,
                "especial"         => 0,
                "usuario_registra" => 1,
                "status"           => 1,
           ]);

           if( !$producto ){
                return;
           }
   
           #   Inserta movimiento de entrada
           $esproducto = ESProducto::create([
               "producto_id"         => $producto->id,
               "almacen_id"          => $almacen_id,
               "referencia"          => "IMPORTACIÓN PLACAS 02/12/21",
               "tipo"                => 1,
               "cantidad"            => $medidasm2,
               "piezas"              => 1,
               "precio"              => $precio,
               "piezas_totales"      => 1,
               "existencias_totales" => $medidasm2,
               "piezas_totales"      => 1,
               "precio_totales"      => $precio,
               "piezas_almacen"      => 1,
               "existencias_almacen" => $medidasm2,
               "precio_almacen"      => $precio,
               "usuario_registra"    => 1
           ]);

           if( !$esproducto ){
               return $producto;
           }
   
   
           #   Actualiza o inserta existencias almacén
           $movimiento = ExistenciasAlmacen::updateOrCreate([
               "producto_id" => $producto->id,
               "almacen_id"  => $almacen_id,
           ],[
               "producto_id" => $producto->id,
               "almacen_id"  => $almacen_id,
               "piezas"      => 1,
               "existencias" => $medidasm2,
               "precio"      => $precio
           ]);

           if( !$movimiento ){
                return $producto;
            }
   
   
           $existenciasmov = ExistenciasMovimiento::create([
               "producto_id"   => $producto->id,
               "almacen_id"    => $almacen_id,
               "movimiento_id" => $movimiento->id,
               "piezas"        => 1,
               "existencias"   => $medidasm2,
               "precio"        => $precio
           ]);

           if( !$existenciasmov ){
                return $producto;
            }
            

            echo ($idx - 1) . " - $producto->id <br>";

           //dd( $row );

        }
        
        return ["success"];
       
    } catch (\Throwable $th) {
        report($th);
    }
//    return $spreadsheet;
});

