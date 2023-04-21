<?php

namespace App\Http\Controllers;

use App\Almacen;
use App\DatosEmpresa;
use App\ESProducto;
use App\ExistenciasAlmacen;
use App\ExistenciasMovimiento;
use App\Http\Requests\GuardarOrdenCompraRequest;
use App\OrdenCompra;
use App\OrdenCompraCuerpo;
use App\OrdenCompraDireccion;
use App\PagoProveedor;
use App\Producto;
use App\Proveedor;
use App\ProveedorProducto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdenCompraController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt")->except(["liquidar"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   Rango de fechas
            $fechainicio = request("fechainicio");
            $fechafin    = request("fechafin");

            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "ordenes_compra" => OrdenCompra::with("proveedor:id,clave,nombre")
                        ->whereBetween("created_at", [$fechainicio, $fechafin])
                        ->get()
                ]
            ], 200);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "proveedores"  => Proveedor::select("id", "clave", "nombre")
                        ->where("status", 1)
                        ->get()
                ]
            ], 200);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GuardarOrdenCompraRequest $request)
    {
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];

            #   Almacén donde surtir
            $almacen_surt = Almacen::where("clave", "BOD")->first();

            #   Total            
            $data = $request->validated();
            $estimado = array_reduce($data["cuerpo"],function ($acumulado, $item){
                return $acumulado + ( $item["cantidad"] * $item["precio_lista"] );
            });    
                    
            #   Guarda encabezado
            $creado = OrdenCompra::create([
                "proveedor_id"     => $data["proveedor_id"],
                "folio"            => $this->nuevoFolio($data["proveedor_id"]),
                "observaciones"    => $data["observaciones"],
                "estimado"         => $estimado,
                "en_almacen"       => $request->en_almacen === true ? 1 : 0,
                "status"           => 1,
                "usuario_registra" => $usuario_id
            ]);

            #   Guarda cuerpo
            foreach ($data["cuerpo"] as $producto) {
                OrdenCompraCuerpo::create([
                    "orden_compra_id" => $creado->id,
                    "producto_id"     => $producto["producto"]["id"],
                    "cantidad"        => $producto["cantidad"],
                    "descripcion"     => $producto["descripcion"],
                    "piezas"          => is_numeric($producto["piezas"]) ? $producto["piezas"] : $producto["cantidad"],
                    "precio_lista"    => $producto["precio_lista"] ?: 0,
                    "precio"          => $producto["precio_lista"] ?: 0,
                ]);
            }

            #   Surtir ----------------------->
            #   Consulta orden de compra creada
            $orden_compra = OrdenCompra::with("cuerpo")->find($creado->id);
            foreach ($orden_compra->cuerpo as $idx => $item) {
                #   Producto de la bd con existencias en almacén
                $productoexs = Producto::with(["existenciasAlmacen"  => function ($query) use ($almacen_surt) {
                    $query->where("almacen_id", $almacen_surt->id);
                }])->find($item->producto_id);
                

                #   Existencias actuales totales
                $existencias_totales = $productoexs->existencias ?: 0;
                $piezas_totales      = $productoexs->piezas ?: 0;
                $precio_totales      = $productoexs->existencias_precio ?: 0;
                #   Existencias actuales almacén
                $exsalmacen          = $productoexs->existenciasAlmacen;

                $existencias_almacen = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->existencias : 0;
                $piezas_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->piezas : 0;
                $precio_almacen      = sizeof($exsalmacen) > 0 ? $exsalmacen[0]->precio : 0;


                #   Nuevas Existencias totales
                $existencias_totales += $item->cantidad;
                $piezas_totales      += $item->piezas;
                $precio_totales      += $item->precio;
                #   Nuevas Existencias almacén
                $existencias_almacen += $item->cantidad;
                $piezas_almacen      += $item->piezas;
                $precio_almacen      += $item->precio;

                #   Inserta movimiento de salida
                $creado = ESProducto::create([
                    "producto_id"         => $item->producto_id,
                    "almacen_id"          => $almacen_surt->id,
                    "referencia"          => $orden_compra->folio,
                    "tipo"                => 1,
                    "cantidad"            => $item->cantidad,
                    "piezas"              => $item->piezas,
                    "precio"              => $item->precio,
                    "piezas_totales"      => $piezas_totales,
                    "existencias_totales" => $existencias_totales,
                    "piezas_totales"      => $piezas_totales,
                    "precio_totales"      => $precio_totales,
                    "piezas_almacen"      => $piezas_almacen,
                    "existencias_almacen" => $existencias_almacen,
                    "precio_almacen"      => $precio_almacen,
                    "usuario_registra"    => $usuario_id
                ]);

                #   Actualiza o inserta existencias almacén
                ExistenciasAlmacen::updateOrCreate([
                    "producto_id" => $productoexs->id,
                    "almacen_id"  => $almacen_surt->id,
                ], [
                    "producto_id" => $productoexs->id,
                    "almacen_id"  => $almacen_surt->id,
                    "piezas"      => $piezas_almacen,
                    "existencias" => $existencias_almacen,
                    "precio"      => $precio_almacen
                ]);

                #   Inserta existencias en almacenes
                $almacenes = Almacen::where("status", 1)->get();
                foreach ($almacenes as $almacen) {
                    $existenciasalm = ExistenciasAlmacen::where("almacen_id", $almacen->id)
                        ->where("producto_id", $item->producto_id)
                        ->get()
                        ->first();
                    ExistenciasMovimiento::create([
                        "producto_id"   => $item->producto_id,
                        "almacen_id"    => $almacen->id,
                        "movimiento_id" => $creado->id,
                        "piezas"        => $existenciasalm ? $existenciasalm->piezas : 0,
                        "existencias"   => $existenciasalm ? $existenciasalm->existencias : 0,
                        "precio"        => $existenciasalm ? $existenciasalm->precio : 0
                    ]);
                }

                #   Actualiza existencias totales
                Producto::where("id", $item->producto_id)->update([
                    "existencias"        => $existencias_totales,
                    "piezas"             => $piezas_totales,
                    "existencias_precio" => $precio_totales
                ]);
            }

            #   Guarda dirección ------------->
            if (!$request->en_almacen) {
                OrdenCompraDireccion::create([
                    "orden_compra_id" => $creado->id,
                    "calle"           => $data["calle"],
                    "numero"          => $data["numero"],
                    "colonia"         => $data["colonia"],
                    "cp"              => $data["cp"],
                    "referencia"      => $data["referencia"],
                    "tipo_obra"       => $data["tipo_obra"],
                    "nombre_recibe"   => $data["nombre_recibe"],
                    "telefono"        => $data["telefono"],
                    "fecha_estimada"  => date("Y-m-d")
                ]);
            }

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["ordencompra" => $orden_compra]
            ], 200);
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = [
                "datosempresa"  => DatosEmpresa::first(),
                "orden_compra"  => OrdenCompra::with("cuerpo.producto.unidad")
                    ->with(["proveedor", "direccion", "uregistra"])
                    ->find($id),
                "PRCOCMP"       => $this->tienePermiso("PRCOCMP")
            ];

            $pdf = app('dompdf.wrapper');
            $pdf->loadView("documentos.ordencompra", $data)
                ->setPaper('letter');
            return $pdf->stream('ordencompra.pdf');
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $orden_compra = OrdenCompra::with("cuerpo.producto.unidad")
                ->with(["proveedor", "direccion", "uregistra"])
                ->find($id);
            #   Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "almacenes"    => Almacen::where("status", 1)
                        ->get(),
                    "orden_compra" => $orden_compra,
                    "productos"    => ProveedorProducto::with("producto.unidad")
                        ->where("proveedor_id", $orden_compra->proveedor_id)
                        ->get()
                ]
            ], 200, []);
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
     * Agrega costos a la órden de compra
     *
     * @param Request $request
     * @return void
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function agregaCostos( Request $request, $id ){
        try {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];    
            #   Consulta orden de compra
            $orden_compra_save = OrdenCompra::find($id);
            $cuerpo_save = OrdenCompraCuerpo::where("orden_compra_id",$id)->get();
            $subtotal = 0;
            #   Itera cuerpo para salvar        
            collect($request->orden_compra["cuerpo"])
                ->each(function($item, $idx) use($cuerpo_save, &$subtotal){                    
                        $cuerpo_save[$idx]->fill([
                                "precio_lista" => $item["precio_lista"],
                                "precio"       => $item["precio_uni"]
                        ])->save();
    
                        $subtotal += ($item["precio_uni"] * $cuerpo_save[$idx]->cantidad); 
                    });
    
            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldoProv($orden_compra_save->proveedor_id);
            #   Actualiza el la suma con el subtotal
            $subtotal  = round($subtotal, 2);
            $iva       = $request->orden_compra["iva"] ? $this->calculaIVA($subtotal) : 0;
            $total     = $subtotal + $iva;
            $observaciones = "CARGO";
            $pago      = $total;
            // ant    desp
            // 100 => 200 => abono => diff +100 
            // 200 => 100 => abono => diff -100 
            //cargo -
            // abono +


            if($orden_compra_save->status == 2)
            {
                $observaciones = $total < $orden_compra_save->total ? "ABONO POR AJUSTE" : "CARGO POR AJUSTE";
                $pago -= $orden_compra_save->total;
            }
            
            $saldo = ($saldo + $pago);

            $orden_compra_save->fill([
                "iva"      => $iva,
                "subtotal" => $subtotal,
                "total"    => $total,
                "status"   => 2
            ])->save();
    
            #   Inserta cargo
            PagoProveedor::create([
                "proveedor_id"         => $orden_compra_save->proveedor_id,
                "orden_compra_id"      => $orden_compra_save->id,
                "orden_compra_pago_id" => $orden_compra_save->id,
                "referencia"           => $orden_compra_save->folio,
                "observaciones"        => $observaciones,
                "importe"              => 0 - $pago,
                "saldo"                => $saldo,
                "fecha_pago"           => $orden_compra_save->created_at,
                "usuario_registra"     => $usuario_id
            ]);

            #   Consulta saldo pendiente
            $saldo = $this->obtenerSaldoProv($orden_compra_save->proveedor_id);

            #   Actualiza saldo pendiente del proveedor
            Proveedor::where("id", $orden_compra_save->proveedor_id)
                ->update([
                    "saldo_pendiente" => $saldo
                ]);
            
            # Response
            return response()->json([
                "head" => "succes",
                "body" => ["ordencompra" => $orden_compra_save]
            ], 200);
                
        } catch (\Throwable $th) {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Consulta los productos que maneja un proveedor
     * junto con sus precios
     *
     * @return void
     */
    public function productosProveedores(String $proveedor_id)
    {
        try {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "productos" => ProveedorProducto::with("producto.unidad")
                        ->where("proveedor_id", $proveedor_id)
                        ->get()
                ]
            ], 200);
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
     * Crea un nuevo folio consultando el último registro 
     * para generar un consecutivo y formatearlo
     *
     * @return void
     */
    private function nuevoFolio($proveedor_id)
    {
        $proveedor = Proveedor::find($proveedor_id);

        $ultimo = OrdenCompra::where(DB::raw("SUBSTRING_INDEX(folio, '-',1)"), $proveedor->clave)
            ->pluck('folio')->last();

        $ultimo = $ultimo ? ((int) explode("-", $ultimo)[1] + 1) : 1;

        $ultimo = str_pad($ultimo, 5, "0", STR_PAD_LEFT);

        return $proveedor->clave . "-" . $ultimo;
    }

    /**
     * Obtener saldo por pagar a proveedor
     *
     * @param int $proveedor_id
     * @return float $saldo
     */
    private function obtenerSaldoProv($proveedor_id)
    {
        $ultimo = PagoProveedor::where("proveedor_id", $proveedor_id)
            ->pluck("saldo")
            ->last();

        return $ultimo ? $ultimo : 0;
    }


    public function prueba()
    {
        $ordenes_compra = OrdenCompra::with("pagos")
            ->get();

        foreach ($ordenes_compra as $orden) {

            $saldo_actual = $this->obtenerSaldoProv($orden->proveedor_id);


            foreach ($orden->pagos as $idx => $pago) {
                // CARGO
                if ($idx == 0) {
                    PagoProveedor::create([
                        "proveedor_id"         => $orden->proveedor_id,
                        "orden_compra_id"      => $orden->id,
                        "orden_compra_pago_id" => 0,
                        "referencia"           => $orden->folio,
                        "observaciones"        => "CARGO",
                        "importe"              => 0 - $orden->capturado,
                        "saldo"                => $saldo_actual + $orden->capturado,
                        "fecha_pago"           => Carbon::now(),
                        "usuario_registra"     => $orden->usuario_registra,
                        "created_at"           => $orden->created_at,
                        "updated_at"           => $orden->created_at,
                    ]);
                }

                // ABONO
                $saldo_actual = $this->obtenerSaldoProv($orden->proveedor_id);
                PagoProveedor::create([
                    "proveedor_id"         => $orden->proveedor_id,
                    "orden_compra_id"      => $orden->id,
                    "orden_compra_pago_id" => $pago->id,
                    "referencia"           => $orden->folio,
                    "observaciones"        => $idx == 0 ? "PAGO INICIAL" : "ABONO",
                    "importe"              => $pago->importe,
                    "saldo"                => $saldo_actual - $pago->importe,
                    "fecha_pago"           => Carbon::now(),
                    "usuario_registra"     => $pago->usuario_registra,
                    "created_at"           => $pago->created_at,
                    "updated_at"           => $pago->created_at,
                ]);
            }
        }


        $proveedores = Proveedor::all();

        foreach ($proveedores as $proveedor) {
            $saldo_actual = $this->obtenerSaldoProv($proveedor->id);
            Proveedor::where("id", $proveedor->id)
                ->update(["saldo_pendiente" => $saldo_actual]);
        }
    }

    public function liquidar()
    {
        $ordenes_compra = OrdenCompra::where("created_at", "<", "2022-07-22")->get();

        foreach ($ordenes_compra as $orden) {
            $proveedor = Proveedor::find($orden->proveedor_id);
            if(!$proveedor) continue;

            $saldo_prov = $this->obtenerSaldoProv($orden->proveedor_id);
            $restante   = $orden->total - $orden->pagado;

            PagoProveedor::create([
                "proveedor_id"         => $orden->proveedor_id,
                "orden_compra_id"      => $orden->id,
                "orden_compra_pago_id" => 0,
                "referencia"           => $orden->folio,
                "observaciones"        => "ABONO",
                "importe"              => $restante,
                "saldo"                => $saldo_prov - $restante,
                "usuario_registra"     => $orden->usuario_registra,
                "fecha_pago"           => $orden->created_at,
                "created_at"           => $orden->created_at,
                "updated_at"           => $orden->created_at,
            ]);

            $orden = OrdenCompra::find($orden->id);
            $orden->pagado += $restante;
            $orden->save();

            $proveedor->saldo_pendiente = $this->obtenerSaldoProv($orden->proveedor_id);
            $proveedor->save();
        }
    }
}
