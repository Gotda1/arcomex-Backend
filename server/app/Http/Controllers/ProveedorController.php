<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarProveedorRequest;
use App\PagoProveedor;
use App\Producto;
use App\Proveedor;
use App\ProveedorProducto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt")->except("reportePagos");
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try
        {
            $adeudo = Proveedor::selectRaw("SUM(saldo_pendiente) AS total")
                ->where("status", 1)
                ->first();
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "adeudo"      =>  $adeudo->total,
                    "proveedores" => Proveedor::all()
                ]
            ], 200);
        }
        catch (\Throwable $e)
        {
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
        try
        {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "productos" => Producto::select("id", "unidad_id", "clave", "nombre")
                        ->with("unidad:id,abreviatura")
                        ->where("status", 1)
                        ->get()
                ]
            ], 200);
        }
        catch (\Throwable $e)
        {
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
    public function store(GuardarProveedorRequest $request)
    {
        try
        {
            #   Array de inserción
            $dataInsert = array_merge($request->validated(), [
                "usuario_registra"  => $request->usuarioDB["id"]
            ]);

            #   Crea proveedor
            $creado = Proveedor::create($dataInsert);

            foreach ($request->productosprov as $producto)
            {
                ProveedorProducto::create([
                    "proveedor_id"  => $creado["id"],
                    "producto_id"   => $producto["producto"]["id"],
                    "precio_lista"        => $producto["precio_lista"],
                ]);
            }

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["proveedor" => $creado]
            ], 200);
        }
        catch (\Throwable $e)
        {
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
        try
        {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "proveedor" => Proveedor::with("productosprov.producto.unidad")
                        ->find($id)
                ]
            ], 200);
        }
        catch (\Throwable $e)
        {
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
        try
        {
            # Response
            return response()->json([
                "head" => "success",
                "body" => [
                    "proveedor" => Proveedor::with("productosprov.producto.unidad")
                        ->find($id)
                ]
            ], 200);
        }
        catch (\Throwable $e)
        {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GuardarProveedorRequest $request, $id)
    {
        try
        {
            #   Actualiza registro
            Proveedor::where("id", $id)->update($request->validated());
            #   Consulta el registro actualizado
            $actualizado = Proveedor::find($id);

            #   Elimina productos y vuelve a inserta
            ProveedorProducto::where("proveedor_id", $id)->delete();
            foreach ($request->productosprov as $producto)
            {
                ProveedorProducto::create([
                    "proveedor_id"  => $id,
                    "producto_id"   => $producto["producto"]["id"],
                    "precio_lista"  => $producto["precio_lista"],
                ]);
            }

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["proveedor" => $actualizado]
            ], 200);
        }
        catch (\Throwable $e)
        {
            report($e);

            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try
        {
            #   Consulta proveedor a borrar
            $proveedor = Proveedor::find($id);
            #   Elimina proveedor y sus tablas hijas 
            Proveedor::destroy($id);
            DB::table("proveedores_productos")->where("proveedor_id", $id)->delete();

            #   Response
            return response()->json([
                "head" => "success",
                "body" => ["proveedor" => $proveedor]
            ], 200, []);
        }
        catch (\Throwable $e)
        {
            report($e);

            #   Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400, []);
        }
    }

    /**
     * Agrega pago a un proveedor
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function agregaPago(Request $request)
    {
        try
        {
            #   id requester
            $usuario_id = request("usuarioDB")["id"];
            #   Consulta saldo pendiente
            $proveedor   = Proveedor::find($request->proveedor_id);
            $saldo       = $proveedor->obtenerSaldo();
            $saldo_nuevo = ($saldo - $request->importe);

            #   Inserta pago proveedor
            PagoProveedor::create([
                "proveedor_id"         => $request->proveedor_id,
                "observaciones"        => $request->observaciones,
                "importe"              => floatval($request->importe),
                "fecha_pago"           => $request->fecha_pago,
                "saldo"                => $saldo_nuevo,
                "usuario_registra"     => $usuario_id
            ]);

            #   Actualiza saldo pendiente del proveedor
            $proveedor->saldo_pendiente = $saldo_nuevo;
            $proveedor->save();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["proveedor" => $proveedor]
            ], 200);
        }
        catch (\Throwable $e)
        {
            report($e);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }
    /**
     * Reporte de pagos por proveedor
     *
     * @param Request $request
     * @return Response
     * @author Guadalupe Ulloa <guadalupe.ulloa@outlook.com>
     */
    public function reportePagos(Request $request)
    {
        try
        {
            #   Rango de fechas
            $fechainicio  = $request->fechainicio;
            $fechafin     = $request->fechafin;
            $proveedor_id = $request->proveedor_id;

            $proveedor   = Proveedor::find($proveedor_id);

            #   Movimientos
            $movimientos = PagoProveedor::where("proveedor_id", $proveedor_id)
                                        ->where("importe", "!=", 0)
                                        ->whereBetween("fecha_pago", [$fechainicio . " 00:00:00", $fechafin . " 23:59:59"])
                                        ->get();

            // return DB::getQueryLog();

            #   Carga libreria de excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            #   Encabezado 1
            $sheet->setCellValue("A1", "RESUMEN DE OPERACIONES {$proveedor->nombre}: $fechainicio - $fechafin");
            $sheet->mergeCells("A1:E1");

            #   Encabezado 2
            $sheet->setCellValue("A2", "Fecha");
            $sheet->setCellValue("B2", "Concepto");
            $sheet->setCellValue("C2", "Pagos");
            $sheet->setCellValue("D2", "Compras");
            $sheet->setCellValue("E2", "Saldo");

            $sheet->getStyle("A1:F2")->getFont()->setBold(true);
            $sheet->getStyle("A1:F2")->getFont()->setSize(14);

            $initRow = 3;
            $movimientos->each(function ($movimiento) use ($sheet, &$initRow)
            {
                #   Setea valores
                $sheet->setCellValue("A" . $initRow, $movimiento->fecha_oc ?: $movimiento->fecha_pago );
                $sheet->setCellValue("B" . $initRow, $movimiento->referencia ?: $movimiento->observaciones);
                $sheet->setCellValue("C" . $initRow, $movimiento->importe < 0 ? "" : $movimiento->importe);
                $sheet->setCellValue("D" . $initRow, $movimiento->importe > 0 ? "" : $movimiento->importe * -1);
                $sheet->setCellValue("E" . $initRow, $movimiento->saldo);
                $initRow++;
            });

            #   Setea totales
            $final = $initRow - 1;
            $sheet->setCellValue("B" . $initRow, "Total");
            $sheet->setCellValue("C" . $initRow, "=SUM(C3:C{$final})");
            $sheet->setCellValue("D" . $initRow, "=SUM(D3:D{$final})");
            $sheet->setCellValue("E" . $initRow, "=D{$initRow}-C{$initRow}");

            $sheet->getStyle("C3:E{$initRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_ACCOUNTING_USD);
            $sheet->getStyle("A{$initRow}:E{$initRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$initRow}:E{$initRow}")->getFont()->setSize(14);

            foreach (range('A', 'E') as $columnID)
                $sheet->getColumnDimension($columnID)->setAutoSize(true);

            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();

            # Response
            return response()->json([
                "head" => "success",
                "body" => ["excel" => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)]
            ], 200);
        }
        catch (\Throwable $th)
        {
            report($th);

            # Response
            return response()->json([
                "head" => "error",
                "body" => ["message" => "Error del servidor"]
            ], 400);
        }
    }
}
