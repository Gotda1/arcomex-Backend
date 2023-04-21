<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\Http\Requests\ImportInventarioRequest;
use App\Http\Requests\ImportProductsRequest;
use App\Imports\InventarioImport;
use App\Imports\ProductosImport;
use App\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportsController extends Controller
{
    public function __construct(){
        $this->middleware("jwt");
    }
    
    /**
     * Importador de pruduvtos
     *
     * @param ImportProductsRequest $request
     * @return Response
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function importarProductos( ImportProductsRequest $request ){ 
        try {
            $import = new ProductosImport();
            $import->import($request->file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $filas =  array_unique(array_map( function($f){ 
                return $f->row();
            }, $failures));

            $messages = array_map( function($f){  
                return "<strong>Error(es) en la fila $f:</strong> <br>";
            }, $filas);
             
            foreach ($failures as $failure) {
                foreach ($failure->errors() as $error){
                    $idx = array_search($failure->row() , $filas);
                    $messages[$idx] .= "$error <br>";
                }
            }

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => $messages]
            ], 400);
        } catch (\Throwable $e) {
            report($e);
            
            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Importador de inventario
     *
     * @param ImportInventarioRequest $request
     * @return void
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function importarInventario( ImportInventarioRequest $request){  
        try { 
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($request->file);
            $worksheet = $spreadsheet->getActiveSheet();

            #   Itera registros para buscar claves inválidas
            $errors = [];
            foreach ($worksheet->toArray() as $idx => $row) {
                if($idx == 0) continue;

                $lineError = "Errores en la línea $idx : ";

                $producto = Producto::where("clave", $row[0])->get()->first();
                $almacen = Almacen::where("clave", $row[2])->get()->first();

                if(!$producto) $lineError .= "El producto $row[0] no existe. ";             
                if(!$almacen) $lineError .= "El almacén $row[2] no existe"; 

                if(!$producto || !$almacen) $errors[] = $lineError . " <br>";
            }
            
            # Response - Si no existe algun productro regresa error
            if(sizeof($errors) > 0){
                return response()->json([
                    "head" => "error",
                    "body" => ["message" => $errors]
                ], 400);
            }

            $referencia = "AJUSTE DESDE EL IMPORTADOR";
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            
            foreach ($worksheet->toArray() as $idx => $row) {
                if($idx == 0) continue;

                $clave        = $row[0];
                $new_cantidad = $row[1];
                $almacen_cve  = $row[2];
                $tipo_mov     = 1;

                #   Almacén
                $almacen = Almacen::where("clave", $almacen_cve)
                                    ->get()
                                    ->first();

                #   Producto de la bd con existencias en almacén
                $producto = Producto::with(["existenciasAlmacen"  => function ($query) use($almacen) {
                                $query->where("almacen_id", $almacen->id);
                            }])->where("clave", $clave )
                            ->get()
                            ->first();
                
                #   Calcula pizas por cantidad
                $new_piezas = $this->calcularUnidades($producto->id, $new_cantidad);

                #   Existencias actuales almacén
                $exsalmacen          = $producto->existenciasAlmacen;
                $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
                $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
                $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;
                            

                #   Si la cantidad es la misma, salta el ajuste
                if($new_cantidad == $existencias_almacen) continue;

                #   Nueva cantidad
                $ajuste_cantidad = $new_cantidad - $existencias_almacen;
                $ajuste_piezas   = $new_piezas - $piezas_almacen;

                $multiplo = 1;
                if($new_cantidad < $existencias_almacen){
                    $tipo_mov = 0;
                    $multiplo = -1;
                }

                
                $esmov = ESProducto::create([
                    "producto_id"         => $producto->id,
                    "almacen_id"          => $almacen->id,
                    "tipo"                => $tipo_mov,
                    "referencia"          => $referencia,
                    "observaciones"       => $referencia,
                    "cantidad"            => ($ajuste_cantidad * $multiplo),
                    "piezas"              => ($ajuste_piezas * $multiplo),
                    "precio"              => 0,
                    "existencias_totales" => $producto->existencias + $ajuste_cantidad,
                    "piezas_totales"      => $producto->piezas + $ajuste_piezas,
                    "precio_totales"      => $producto->existencias_precio,
                    "existencias_almacen" => $new_cantidad,
                    "piezas_almacen"      => $new_piezas,
                    "precio_almacen"      => $precio_almacen,
                    "usuario_registra"    => $usuario_id
                ]);

                #   Actualiza existencias totales
                Producto::where("id", $producto->id)->update([
                    "existencias"        => $producto->existencias + $ajuste_cantidad,
                    "piezas"             => $producto->piezas + $ajuste_piezas,
                    "existencias_precio" => $producto->existencias_precio
                ]);

                #   Actualiza o inserta existencias almacén
                ExistenciasAlmacen::updateOrCreate([
                    "producto_id" => $producto->id,
                    "almacen_id"  => $almacen->id,
                ],[
                    "producto_id" => $producto->id,
                    "almacen_id"  => $almacen->id,
                    "piezas"      => $new_piezas,
                    "existencias" => $new_cantidad,
                    "precio"      => $precio_almacen
                ]);
                
                #   Inserta existencias en almacenes
                $almacenes = Almacen::where("status", 1)
                                    ->get();

                foreach ($almacenes as $alm) {
                    $existenciasalm = ExistenciasAlmacen::where("almacen_id", $alm->id)
                                                        ->where("producto_id", $producto->id)
                                                        ->get()
                                                        ->first();
                    ExistenciasMovimiento::create([
                        "producto_id"   => $producto->id,
                        "almacen_id"    => $alm->id,
                        "movimiento_id" => $esmov->id,
                        "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                        "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                        "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                    ]);
                }
            }
        } catch (\Throwable $e) {
            report($e);
            
            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }
}
