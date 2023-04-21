<?php

use App\Http\Controllers\ImportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix("dashboard")->group(function () {
    Route::get("contadores","DashboardController@contadores");
    Route::get("productos-prov","DashboardController@catalogoProveedores"); 
    Route::get("clientes-ultimas","DashboardController@clientesUltimasCompras");
    Route::get("seguimiento-cotizaciones","DashboardController@seguimientoCotizaciones");
    Route::get("pedidos","DashboardController@pedidos");
    Route::get("inventario","DashboardController@inventario");     
    Route::get("almacenes","DashboardController@almacenes"); 
    Route::get("proveedores","DashboardController@pagosProveedores"); 
    Route::get("clientes","DashboardController@pagosClientes"); 
    Route::get("movimientos-clientes","DashboardController@movimientosClientes"); 
    Route::get("ventas-producto","DashboardController@ventasPorProducto"); 
    Route::get("pedidos-surtidos","DashboardController@reportePedidosSurtidos"); 
});


Route::get("usuario/destroyall", "UsuarioController@destroySessions");
Route::resource("usuario","UsuarioController");

Route::get("prospecto/convertir-cliente/{id}","ProspectoController@convertirACliente");
Route::resource("prospecto","ProspectoController");

Route::resource("cliente","ClienteController");
Route::resource("producto","ProductoController");
Route::get("proveedor/reporte","ProveedorController@reportePagos"); 
Route::resource("proveedor","ProveedorController");
Route::post("proveedor/pago","ProveedorController@agregaPago"); 

#   Cotizaciones
#   Catálogo de adquisidores
Route::get("cotizacion/catadquisidor/{catalogo}","CotizacionController@catAdquisodores");
#   Pasar cotización a pedido
Route::put("cotizacion/a-pedido/{id}","CotizacionController@aPedido");
Route::resource("cotizacion","CotizacionController");
Route::get("cotizacion/duplicate/{id}","CotizacionController@duplicate");

#   Listado de cotizaciones
Route::get("cotizacion/cancelar/{id}","CotizacionController@cancelar");
Route::get("cotizacion-obervaciones","CotizacionController@listarObservaciones");
Route::post("cotizacion-obervaciones","CotizacionController@guardarObservacion");
#   Pedidos
#   Data a surtir de un pedido
Route::get("pedido/vendedores", "PedidosController@showVendedores");
Route::get("pedido/surtir/{id}","PedidosController@mostrarPedidoSurtir");
Route::post("pedido/surtir/{id}","PedidosController@surtirPedido");
Route::get("pedido/surtir-completo/{id}","PedidosController@surtirPedidoCompleto");
Route::post("pedido/pago/{id}","PedidosController@agregaPago");
Route::resource("pedido","PedidosController");
#   Status Cotización Compra
Route::put("cotizacion-compra/cambiar-status/{id}","CotizacionCompraController@updateStatus");
#   Cotización compra
Route::resource("cotizacion-compra","CotizacionCompraController");
#   Órdenes de compra
#   Productos de proveedor
Route::get("orden-compra/productos-prov/{id}","OrdenCompraController@productosProveedores");
#   Ordenes compra
#   Guardar costos
Route::post("orden-compra/pago/{id}","OrdenCompraController@agregaPago");
Route::post("orden-compra/guarda-costos/{id}","OrdenCompraController@agregaCostos");
Route::get("orden-compra/productos-prov/{id}","OrdenCompraController@productosProveedores");
Route::resource("orden-compra","OrdenCompraController");


#   Entradas y salidas de productos
#   Catálogo de almacenes
Route::get("es-producto/show-almacenes", "ESProductoController@showAlmacenes");
Route::resource("es-producto","ESProductoController");
#   Existencias
Route::get("existencias/show-almacenes", "ExistenciasController@showAlmacenes");
Route::resource("existencias", "ExistenciasController");

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');

Route::prefix("importador")->group(function () {
    Route::post("productos", "ImportsController@importarProductos");
    Route::post("existencias", "ImportsController@importarInventario");
});

Route::get("prueba", "DashboardController@prueba");
